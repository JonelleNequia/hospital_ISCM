<?php
require_once __DIR__ . '/../models/GRNModel.php';
require_once __DIR__ . '/../models/POModel.php';

class BillingService {
    private $app;
    private $grnModel;
    private $poModel;

    public function __construct($app) {
        $this->app = $app;
        $this->grnModel = new GRNModel($app->db);
        $this->poModel = new POModel($app->db);
    }

    public function sendGrnToBilling(int $grnId): array {
        // 1. Fetch GRN data
        $grnData = $this->getGrnData($grnId);

        // 2. Get billing system config
        $billingConfig = $this->app->config['billing'];

        // 3. Send data to billing system
        return $this->sendToBillingAPI($billingConfig, $grnData);
    }

    private function getGrnData(int $grnId): array {
        // This is a simplified example. You'll need to fetch the actual data.
        // This might involve joining a few tables (goods_receipts, goods_receipt_items, items, suppliers, purchase_orders)
        $st = $this->app->db->prepare(
            "SELECT gr.id as grn_id, gr.grn_no, po.po_no, s.name as supplier_name, i.name as item_name, gri.qty_received, poi.unit_price
            FROM goods_receipts gr
            JOIN goods_receipt_items gri ON gr.id = gri.grn_id
            JOIN purchase_orders po ON gr.po_id = po.id
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN items i ON gri.item_id = i.id
            JOIN po_items poi ON gri.po_item_id = poi.id
            WHERE gr.id = ?"
        );
        $st->execute([$grnId]);
        return $st->fetchAll();
    }

    private function sendToBillingAPI(array $config, array $data): array {
        $url = $config['api_url'];
        $apiKey = $config['api_key'];

        $payload = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErr = $curlErrNo ? curl_error($ch) : null;
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Failed to send GRN to billing system. GRN ID: " . ($data[0]['grn_id'] ?? 'unknown') . " HTTP Code: $httpCode, Response: $response");
        }

        return [
            'http_code' => $httpCode,
            'response' => $response,
            'curl_error_no' => $curlErrNo,
            'curl_error' => $curlErr,
        ];
    }
}
