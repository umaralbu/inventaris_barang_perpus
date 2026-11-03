<?php

namespace Tests\Feature;

use App\Enums\ItemCondition;
use App\Enums\ItemUnitStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCatalog;
use App\Models\Location;
use App\Models\Staff;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    public function test_item_catalog_relations_and_accessor(): void
    {
        $catalog = ItemCatalog::with(['category', 'units'])->first();
        $this->assertNotNull($catalog);
        $this->assertInstanceOf(Category::class, $catalog->category);
        $this->assertGreaterThan(0, $catalog->units->count());
        $this->assertIsInt($catalog->available_units_count);
    }

    public function test_item_relations_and_enum_casts(): void
    {
        $item = Item::with(['catalog', 'category', 'location', 'transactionDetails'])->first();
        $this->assertNotNull($item);
        $this->assertInstanceOf(ItemCatalog::class, $item->catalog);
        $this->assertInstanceOf(Category::class, $item->category);
        $this->assertInstanceOf(Location::class, $item->location);
        $this->assertInstanceOf(ItemCondition::class, $item->condition);
        $this->assertInstanceOf(ItemUnitStatus::class, $item->status);
    }

    public function test_transaction_relations_and_enum_casts(): void
    {
        $transaction = Transaction::with(['details', 'staff', 'user'])->first();
        $this->assertNotNull($transaction);
        $this->assertInstanceOf(TransactionType::class, $transaction->type);
        $this->assertInstanceOf(TransactionStatus::class, $transaction->status);
        $this->assertGreaterThan(0, $transaction->details->count());
        $this->assertInstanceOf(Staff::class, $transaction->staff);
    }

    public function test_transaction_detail_relations(): void
    {
        $detail = TransactionDetail::with(['transaction', 'item', 'fromLocation', 'toLocation'])->first();
        $this->assertNotNull($detail);
        $this->assertInstanceOf(Transaction::class, $detail->transaction);
        $this->assertInstanceOf(Item::class, $detail->item);
        $this->assertInstanceOf(ItemCondition::class, $detail->condition_before);
    }

    public function test_user_model_role_enum_and_helpers(): void
    {
        $user = User::first();
        $this->assertNotNull($user);
        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertIsBool($user->isAdmin());
        $this->assertIsBool($user->isPegawai());
    }

    public function test_category_location_staff_relations(): void
    {
        $category = Category::with(['catalogs', 'items'])->first();
        $this->assertNotNull($category);
        $this->assertGreaterThan(0, $category->catalogs->count());

        $location = Location::with('items')->first();
        $this->assertNotNull($location);

        $staff = Staff::with('transactions')->first();
        $this->assertNotNull($staff);
    }
}

