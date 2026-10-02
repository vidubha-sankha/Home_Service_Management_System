<?php
require_once __DIR__ . '/SmsService.php';
require_once __DIR__ . '/EmailService.php';

class NotificationService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    private function getSetting($key) {
        $stmt = $this->conn->prepare("SELECT setting_value FROM SystemSettings WHERE setting_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row['setting_value'];
        }
        return 'ON'; // default
    }

    private function logNotification($userId, $userType, $reqId, $channel, $type, $recipient, $msg, $status, $providerId, $error) {
        $stmt = $this->conn->prepare("INSERT INTO NotificationLog (user_id, user_type, request_id, channel, notification_type, recipient, message, status, provider_message_id, error_message, sent_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $sent_at = ($status === 'SENT') ? date('Y-m-d H:i:s') : null;
        $stmt->bind_param("isissssssss", $userId, $userType, $reqId, $channel, $type, $recipient, $msg, $status, $providerId, $error, $sent_at);
        $stmt->execute();
    }

    private function hasBeenSent($reqId, $channel, $type, $userType) {
        $stmt = $this->conn->prepare("SELECT id FROM NotificationLog WHERE request_id = ? AND channel = ? AND notification_type = ? AND user_type = ? AND status = 'SENT'");
        $stmt->bind_param("isss", $reqId, $channel, $type, $userType);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private function addInAppNotification($userId, $userType, $reqId, $msg) {
        $stmt = $this->conn->prepare("INSERT INTO InAppNotification (user_id, user_type, request_id, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $userId, $userType, $reqId, $msg);
        $stmt->execute();
    }

    private function sendChannel($userId, $userType, $reqId, $channel, $type, $recipient, $subject, $msg) {
        if ($this->hasBeenSent($reqId, $channel, $type, $userType)) {
            return; // prevent duplicate
        }

        if ($channel === 'EMAIL' && $this->getSetting('email_notifications') !== 'ON') return;
        if ($channel === 'SMS' && $this->getSetting('sms_notifications') !== 'ON') return;

        $result = ['status' => false, 'provider_id' => null, 'error' => 'Unknown channel'];
        
        try {
            if ($channel === 'EMAIL') {
                $result = EmailService::sendEmail($recipient, $subject, $msg);
            } elseif ($channel === 'SMS') {
                $result = SmsService::sendSms($recipient, $msg);
            }
            $status = $result['status'] ? 'SENT' : 'FAILED';
            $this->logNotification($userId, $userType, $reqId, $channel, $type, $recipient, $msg, $status, $result['provider_id'], $result['error']);
        } catch (Exception $e) {
            $this->logNotification($userId, $userType, $reqId, $channel, $type, $recipient, $msg, 'FAILED', null, $e->getMessage());
        }
    }

    private function getRequestDetails($reqId) {
        $stmt = $this->conn->prepare("
            SELECT sr.*, c.Name as CustomerName, c.Email as CustomerEmail, c.Phone as CustomerPhone,
                   sc.ServiceName, e.Name as TechnicianName, e.Phone as TechnicianPhone, e.Email as TechnicianEmail
            FROM ServiceRequest sr
            JOIN Customer c ON sr.CustomerID = c.CustomerID
            JOIN ServiceCategory sc ON sr.ServiceID = sc.ServiceID
            LEFT JOIN Employee e ON sr.EmployeeID = e.EmployeeID
            WHERE sr.RequestID = ?
        ");
        $stmt->bind_param("i", $reqId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function notifyRequestCreated($reqId) {
        $req = $this->getRequestDetails($reqId);
        if (!$req) return;

        // Customer Email
        $custEmailSubj = "HSMS - Service Request Received";
        $custEmailMsg = "Hello {$req['CustomerName']},\nYour home service request has been successfully submitted.\nRequest ID: #{$reqId}\nService: {$req['ServiceName']}\nPreferred Date: {$req['PreferredDate']}\nPreferred Time: {$req['PreferredTime']}\nAddress: {$req['Address']}\nStatus: Pending\nOur administrator will review your request and assign a suitable technician.\nThank you,\nHSMS Team";
        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'EMAIL', 'CREATED', $req['CustomerEmail'], $custEmailSubj, $custEmailMsg);

        // Customer SMS
        $custSms = "HSMS: Your service request #{$reqId} for {$req['ServiceName']} has been received successfully. Status: Pending.";
        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'SMS', 'CREATED', $req['CustomerPhone'], '', $custSms);
        
        $this->addInAppNotification($req['CustomerID'], 'CUSTOMER', $reqId, "Your request #{$reqId} for {$req['ServiceName']} has been submitted.");

        // Admin Email/SMS (Fetch an admin)
        $admin = $this->conn->query("SELECT AdminID, Email, Phone FROM Admin LIMIT 1")->fetch_assoc();
        if ($admin) {
            $adminEmailSubj = "HSMS - New Service Request";
            $adminEmailMsg = "New Request #{$reqId}\nCustomer: {$req['CustomerName']} ({$req['CustomerPhone']})\nService: {$req['ServiceName']}\nDate/Time: {$req['PreferredDate']} {$req['PreferredTime']}\nAddress: {$req['Address']}\nEmergency: " . ($req['IsEmergency'] ? 'Yes' : 'No') . "\nDescription: {$req['Description']}";
            $this->sendChannel($admin['AdminID'], 'ADMIN', $reqId, 'EMAIL', 'CREATED', $admin['Email'], $adminEmailSubj, $adminEmailMsg);
            
            $adminSms = "HSMS: New service request #{$reqId} received from {$req['CustomerName']} for {$req['ServiceName']}. Please login to assign a technician.";
            if ($admin['Phone']) {
                $this->sendChannel($admin['AdminID'], 'ADMIN', $reqId, 'SMS', 'CREATED', $admin['Phone'], '', $adminSms);
            }
            $this->addInAppNotification($admin['AdminID'], 'ADMIN', $reqId, "New service request #{$reqId} from {$req['CustomerName']}.");
        }
    }

    public function notifyTechnicianAssigned($reqId) {
        $req = $this->getRequestDetails($reqId);
        if (!$req || !$req['EmployeeID']) return;

        // Customer Email
        $custEmailSubj = "HSMS - Technician Assigned";
        $custEmailMsg = "Request ID: #{$reqId}\nService: {$req['ServiceName']}\nTechnician Name: {$req['TechnicianName']}\nTechnician Phone: {$req['TechnicianPhone']}\nPreferred Date: {$req['PreferredDate']}\nPreferred Time: {$req['PreferredTime']}\nAddress: {$req['Address']}\nCurrent Status: Assigned";
        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'EMAIL', 'ASSIGNED', $req['CustomerEmail'], $custEmailSubj, $custEmailMsg);

        // Customer SMS
        $custSms = "HSMS: Technician {$req['TechnicianName']} has been assigned to your service request #{$reqId}. Date: {$req['PreferredDate']}, Time: {$req['PreferredTime']}.";
        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'SMS', 'ASSIGNED', $req['CustomerPhone'], '', $custSms);
        
        $this->addInAppNotification($req['CustomerID'], 'CUSTOMER', $reqId, "Technician {$req['TechnicianName']} assigned to request #{$reqId}.");

        // Technician Email
        $techEmailSubj = "HSMS - New Service Request Assigned";
        $techEmailMsg = "Request ID: #{$reqId}\nCustomer Name: {$req['CustomerName']}\nCustomer Phone: {$req['CustomerPhone']}\nService: {$req['ServiceName']}\nAddress: {$req['Address']}\nPreferred Date: {$req['PreferredDate']}\nPreferred Time: {$req['PreferredTime']}\nEmergency Status: " . ($req['IsEmergency'] ? 'Yes' : 'No') . "\nProblem Description: {$req['Description']}";
        $this->sendChannel($req['EmployeeID'], 'TECHNICIAN', $reqId, 'EMAIL', 'ASSIGNED', $req['TechnicianEmail'], $techEmailSubj, $techEmailMsg);

        // Technician SMS
        $techSms = "HSMS: New {$req['ServiceName']} job #{$reqId} assigned to you. Customer: {$req['CustomerName']}. Date: {$req['PreferredDate']}, Time: {$req['PreferredTime']}. Login to HSMS for details.";
        $this->sendChannel($req['EmployeeID'], 'TECHNICIAN', $reqId, 'SMS', 'ASSIGNED', $req['TechnicianPhone'], '', $techSms);
        
        $this->addInAppNotification($req['EmployeeID'], 'TECHNICIAN', $reqId, "New {$req['ServiceName']} request #{$reqId} assigned to you.");
    }

    public function notifyServiceStarted($reqId) {
        $req = $this->getRequestDetails($reqId);
        if (!$req) return;

        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'EMAIL', 'STARTED', $req['CustomerEmail'], "HSMS - Service Started", "Your service request #{$reqId} is now in progress.");
        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'SMS', 'STARTED', $req['CustomerPhone'], '', "HSMS: Your service request #{$reqId} is now in progress. Technician: {$req['TechnicianName']}.");
        $this->addInAppNotification($req['CustomerID'], 'CUSTOMER', $reqId, "Service request #{$reqId} is now in progress.");
    }

    public function notifyServiceCompleted($reqId) {
        $req = $this->getRequestDetails($reqId);
        if (!$req) return;

        $emailMsg = "Request ID: #{$reqId}\nService: {$req['ServiceName']}\nTechnician: {$req['TechnicianName']}\nCompleted Date: " . date('Y-m-d') . "\nPlease login to HSMS to leave a review and rating.";
        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'EMAIL', 'COMPLETED', $req['CustomerEmail'], "HSMS - Service Completed", $emailMsg);
        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'SMS', 'COMPLETED', $req['CustomerPhone'], '', "HSMS: Your service request #{$reqId} has been completed. Thank you for using HSMS. Please leave a rating/review.");
        $this->addInAppNotification($req['CustomerID'], 'CUSTOMER', $reqId, "Service request #{$reqId} has been completed. Please review.");
    }

    public function notifyRequestCancelled($reqId) {
        $req = $this->getRequestDetails($reqId);
        if (!$req) return;

        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'EMAIL', 'CANCELLED', $req['CustomerEmail'], "HSMS - Service Request Cancelled", "Your service request #{$reqId} has been cancelled.");
        $this->sendChannel($req['CustomerID'], 'CUSTOMER', $reqId, 'SMS', 'CANCELLED', $req['CustomerPhone'], '', "HSMS: Your service request #{$reqId} has been cancelled. Please login to HSMS for more details.");
        $this->addInAppNotification($req['CustomerID'], 'CUSTOMER', $reqId, "Service request #{$reqId} was cancelled.");

        if ($req['EmployeeID']) {
            $this->sendChannel($req['EmployeeID'], 'TECHNICIAN', $reqId, 'SMS', 'CANCELLED', $req['TechnicianPhone'], '', "HSMS: Service request #{$reqId} assigned to you has been cancelled. Login to HSMS for details.");
            $this->addInAppNotification($req['EmployeeID'], 'TECHNICIAN', $reqId, "Request #{$reqId} was cancelled.");
        }
    }
}
