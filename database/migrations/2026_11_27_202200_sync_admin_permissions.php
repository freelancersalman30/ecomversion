<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $neededPermissions = [
            'order-list', 'order manage',
            'product-list', 'product-create', 'product-edit', 'product-delete',
            'category-list', 'category-create', 'category-edit', 'category-delete',
            'subcategory-list', 'subcategory-create', 'subcategory-edit', 'subcategory-delete',
            'childcategory-list', 'childcategory-create', 'childcategory-edit', 'childcategory-delete',
            'brand-list', 'brand-create', 'brand-edit', 'brand-delete',
            'color-list', 'color-create', 'color-edit', 'color-delete',
            'size-list', 'size-create', 'size-edit', 'size-delete',
            'user-list', 'user-create', 'user-edit', 'user-delete',
            'role-list', 'role-create', 'role-edit', 'role-delete',
            'permission-list', 'permission-create', 'permission-edit', 'permission-delete',
            'setting-list', 'setting-create', 'setting-edit', 'setting-delete',
            'shipping-list', 'shipping-create', 'shipping-edit', 'shipping-delete',
            'contact-list', 'contact-create', 'contact-edit', 'contact-delete',
            'page-list', 'page-create', 'page-edit', 'page-delete',
            'social-list', 'social-create', 'social-edit', 'social-delete',
            'banner-list', 'banner-create', 'banner-edit', 'banner-delete',
            'banner-category-list', 'banner-category-create', 'banner-category-edit', 'banner-category-delete',
            'coupon-list', 'coupon-create', 'coupon-edit', 'coupon-delete',
            'review-list', 'review-create', 'review-edit', 'review-delete',
            'campaign-list', 'campaign-create', 'campaign-edit', 'campaign-delete',
            'fraud-check',
            'sms-send',
            'email-setting-list', 'email-setting-edit',
            'fraud-setting-list', 'fraud-setting-edit',
            'api-manage',
            'pixel-manage',
            'seo-manage',
            'sitemap-manage',
            'customer-list', 'customer-create', 'customer-edit', 'customer-delete',
            'expense-manage', 'fund-manage', 'purchase-manage', 'supplier-manage', 'report-manage'
        ];

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'admin']);

        foreach ($neededPermissions as $permName) {
            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'admin']);
            if (!$adminRole->hasPermissionTo($permName)) {
                $adminRole->givePermissionTo($perm);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
