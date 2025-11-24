<?php
/**
 * Permission & Invoice Item Handler Tests
 * Run with: phpunit test-invoice-handlers.php
 */

require_once dirname(__FILE__) . '/../../../../wp-load.php';

use PHPUnit\Framework\TestCase;

class InvoiceHandlerTests extends TestCase {
    
    protected $invoice_id;
    protected $user_id;
    
    public function setUp(): void {
        // Set up admin user
        $this->user_id = 1;
        wp_set_current_user($this->user_id);
        
        // Get or create a test invoice
        global $wpdb;
        $table = $wpdb->prefix . 'ays_invoices';
        $existing = $wpdb->get_row("SELECT id FROM $table LIMIT 1");
        
        if ($existing) {
            $this->invoice_id = $existing->id;
        } else {
            // Create test invoice
            $wpdb->insert($table, [
                'client_id' => 1,
                'status' => 'draft',
                'subtotal' => 0,
                'tax_amount' => 0,
                'total' => 0,
                'created_at' => current_time('mysql'),
            ]);
            $this->invoice_id = $wpdb->insert_id;
        }
    }
    
    /**
     * Test 1: User is logged in as admin
     */
    public function testUserIsLoggedInAsAdmin() {
        $this->assertTrue(is_user_logged_in(), 'User should be logged in');
        $this->assertTrue(current_user_can('manage_options'), 'User should have manage_options capability');
        $this->assertEquals(1, get_current_user_id(), 'User ID should be 1');
    }
    
    /**
     * Test 2: Check database table schema
     */
    public function testInvoiceItemsTableSchema() {
        global $wpdb;
        $table = $wpdb->prefix . 'ays_invoice_items';
        
        // Get table columns
        $columns = $wpdb->get_results("DESCRIBE $table");
        $column_names = wp_list_pluck($columns, 'Field');
        
        echo "\n=== Invoice Items Table Schema ===\n";
        echo "Table: $table\n";
        echo "Columns: " . implode(', ', $column_names) . "\n";
        
        // Check for critical columns
        $this->assertContains('id', $column_names, 'Should have id column');
        $this->assertContains('invoice_id', $column_names, 'Should have invoice_id column');
        $this->assertContains('description', $column_names, 'Should have description column');
        
        // Check if qty or quantity exists
        $has_qty = in_array('qty', $column_names);
        $has_quantity = in_array('quantity', $column_names);
        
        echo "Has 'qty' column: " . ($has_qty ? 'YES' : 'NO') . "\n";
        echo "Has 'quantity' column: " . ($has_quantity ? 'YES' : 'NO') . "\n";
        
        $this->assertTrue($has_qty || $has_quantity, 'Should have qty or quantity column');
    }
    
    /**
     * Test 3: Nonce generation and verification
     */
    public function testNonceGeneration() {
        $action = 'ays_add_invoice_item_' . $this->invoice_id;
        $nonce = wp_create_nonce($action);
        
        echo "\n=== Nonce Test ===\n";
        echo "Action: $action\n";
        echo "Generated Nonce: $nonce\n";
        
        // Verify nonce
        $verified = wp_verify_nonce($nonce, $action);
        echo "Verification Result: $verified\n";
        
        $this->assertNotFalse($verified, 'Nonce should verify');
    }
    
    /**
     * Test 4: Mock form submission data
     */
    public function testFormDataStructure() {
        $nonce_action = 'ays_add_invoice_item_' . $this->invoice_id;
        $nonce = wp_create_nonce($nonce_action);
        
        $_POST = [
            'invoice_id' => $this->invoice_id,
            'ays_add_item_nonce' => $nonce,
            'description' => 'Test Item',
            'quantity' => 5,
            'rate' => 100.00,
            'taxable' => 1,
        ];
        
        echo "\n=== Form Data ===\n";
        echo "POST data: " . json_encode($_POST, JSON_PRETTY_PRINT) . "\n";
        
        // Verify all required fields present
        $this->assertArrayHasKey('invoice_id', $_POST);
        $this->assertArrayHasKey('ays_add_invoice_item_nonce', $_POST);
        $this->assertArrayHasKey('description', $_POST);
        $this->assertEquals($this->invoice_id, $_POST['invoice_id']);
    }
    
    /**
     * Test 5: Check if handler class exists
     */
    public function testHandlerClassExists() {
        $this->assertTrue(class_exists('AYS_Invoices_Tab'), 'AYS_Invoices_Tab class should exist');
        $this->assertTrue(method_exists('AYS_Invoices_Tab', 'handle_add_invoice_item'), 'handle_add_invoice_item method should exist');
    }
    
    /**
     * Test 6: Simulate actual database insert
     */
    public function testDatabaseInsert() {
        global $wpdb;
        $table = $wpdb->prefix . 'ays_invoice_items';
        
        $insert = $wpdb->insert($table, [
            'invoice_id' => $this->invoice_id,
            'description' => 'PHPUnit Test Item ' . time(),
            'qty' => 2,
            'rate' => 50.00,
            'taxable' => 1,
            'line_total' => 100.00,
            'line_tax' => 10.00,
            'hash' => md5(uniqid('test_', true)),
            'status' => 'active',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);
        
        $this->assertNotFalse($insert, 'Insert should succeed. Error: ' . $wpdb->last_error);
        
        echo "\n=== Database Insert Test ===\n";
        echo "Inserted item ID: " . $wpdb->insert_id . "\n";
        echo "Invoice ID: " . $this->invoice_id . "\n";
    }
    
    /**
     * Test 7: Check column name being used in code
     */
    public function testCodeUsesCorrectColumnName() {
        $source = file_get_contents(dirname(__FILE__) . '/includes/invoices/ays-class-invoices-tab.php');
        
        // Search for the column reference in the code
        preg_match_all('/\[\'(qty|quantity)\'\]/', $source, $matches);
        
        echo "\n=== Code Column References ===\n";
        echo "Found references to: " . implode(', ', array_unique($matches[1])) . "\n";
        
        // Check database
        global $wpdb;
        $table = $wpdb->prefix . 'ays_invoice_items';
        $columns = $wpdb->get_results("DESCRIBE $table");
        $column_names = wp_list_pluck($columns, 'Field');
        
        echo "Database has: " . implode(', ', $column_names) . "\n";
        
        $this->assertNotEmpty($matches[1], 'Code should reference qty or quantity');
    }
}
