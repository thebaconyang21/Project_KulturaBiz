<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_user_role_helpers_match_assigned_role_and_approval_status(): void
    {
        $admin = new User(['role' => 'admin', 'status' => 'approved']);
        $artisan = new User(['role' => 'artisan', 'status' => 'pending']);
        $customer = new User(['role' => 'customer', 'status' => 'approved']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isArtisan());
        $this->assertTrue($artisan->isArtisan());
        $this->assertFalse($artisan->isApproved());
        $this->assertTrue($customer->isCustomer());
        $this->assertTrue($customer->isApproved());
    }

    public function test_order_status_helpers_provide_expected_labels_and_badges(): void
    {
        $order = new Order(['status' => 'shipped', 'total_amount' => 1499.5]);

        $this->assertSame('Shipped', $order->status_label);
        $this->assertSame('badge-primary', $order->status_badge);
        $this->assertSame('₱1,499.50', $order->formatted_total);
    }
}
