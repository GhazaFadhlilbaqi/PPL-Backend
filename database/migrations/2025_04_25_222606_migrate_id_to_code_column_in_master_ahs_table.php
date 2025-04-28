<?php

use App\Models\Ahs;
use App\Models\AhsItem;
use App\Models\MasterRabItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateIdToCodeColumnInMasterAhsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Add code column
        Schema::table('ahs', function (Blueprint $table) {
            $table->string('code')->after('id');
        });

        // 2. Copy id into code
        Ahs::orderBy('created_at')->chunk(500, function ($ahses) {
            foreach ($ahses as $ahs) {
                $ahs->code = $ahs->id;
                $ahs->saveQuietly();
            }
        });

        // 3. Modify id to BIGINT AUTO_INCREMENT
        Schema::table('ahs_items', function (Blueprint $table) {
            $table->dropForeign('ahs_items_ahs_id_foreign');
        });
        Schema::table('master_rab_items', function (Blueprint $table) {
            $table->dropForeign('fk_master_rab_items_ahs_id');
        });
        Schema::table('ahs', function (Blueprint $table) {
            $table->dropPrimary();
        });

        // 4. Manually setup ahs id incrementally
        $count = 1;
        Ahs::orderBy('created_at')->chunk(500, function ($rows) use (&$count) {
            foreach ($rows as $row) {
                $row->id = $count;
                $row->saveQuietly();
                $count++;
            }
        });

        // 5. Set ahs id type as primary key
        Schema::table('ahs', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable()->change();
        });
        DB::statement('ALTER TABLE ahs MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 6. Change ahs_id with big integer id
        AhsItem::chunk(500, function ($items) {
            foreach ($items as $item) {
                $ahs = Ahs::where('code', $item->ahs_id)->first();
                if ($ahs) {
                    $item->ahs_id = $ahs->id;
                    $item->saveQuietly();
                }
            }
        });
        MasterRabItem::chunk(500, function ($items) {
            foreach ($items as $item) {
                $ahs = Ahs::where('code', $item->ahs_id)->first();
                if ($ahs) {
                    $item->ahs_id = $ahs->id;
                    $item->saveQuietly();
                }
            }
        });

        // 7. Set ahs_id type from varchar into big integer
        Schema::table('ahs_items', function (Blueprint $table) {
            $table->unsignedBigInteger('ahs_id')->change();
        });
        Schema::table('master_rab_items', function (Blueprint $table) {
            $table->unsignedBigInteger('ahs_id')->change();
        });

        // 8. Recreate foreign keys
        Schema::table('ahs_items', function (Blueprint $table) {
            $table->foreign('ahs_id')->references('id')->on('ahs')->onDelete('cascade');
        });
        Schema::table('master_rab_items', function (Blueprint $table) {
            $table->foreign('ahs_id')->references('id')->on('ahs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 1. Drop foreign key references
        Schema::table('ahs_items', function (Blueprint $table) {
            $table->dropForeign('ahs_items_ahs_id_foreign');
        });
        Schema::table('master_rab_items', function (Blueprint $table) {
            $table->dropForeign('master_rab_items_ahs_id_foreign');
        });

        // 2. Restore id as varchar & remove auto increment
        Schema::table('ahs', function (Blueprint $table) {
            $table->string('id')->change();
        });
        Schema::table('ahs_items', function (Blueprint $table) {
            $table->string('ahs_id')->change();
        });
        Schema::table('master_rab_items', function (Blueprint $table) {
            $table->string('ahs_id')->change();
        });

        // 3. Drop id as primary key
        Schema::table('ahs', function (Blueprint $table) {
            $table->dropPrimary();
        });

        // 4. Restore id values from code
        AhsItem::chunk(500, function ($items) {
            foreach ($items as $item) {
                $ahs = Ahs::where('id', $item->ahs_id)->first();
                if ($ahs) {
                    $item->ahs_id = $ahs->code;
                    $item->saveQuietly();
                }
            }
        });
        MasterRabItem::chunk(500, function ($items) {
            foreach ($items as $item) {
                $ahs = Ahs::where('id', $item->ahs_id)->first();
                if ($ahs) {
                    $item->ahs_id = $ahs->code;
                    $item->saveQuietly();
                }
            }
        });
        Ahs::orderBy('created_at')->chunk(500, function ($ahses) {
            foreach ($ahses as $ahs) {
                $ahs->id = $ahs->code;
                $ahs->saveQuietly();
            }
        });

        // 5. Re-set id as primary key
        Schema::table('ahs', function (Blueprint $table) {
            $table->primary('id');
        });

        // 6. Recreate foreign keys 
        Schema::table('ahs_items', function (Blueprint $table) {
            $table->foreign('ahs_id')->references('id')->on('ahs')->onUpdate('cascade')->onDelete('cascade');
        });
        Schema::table('master_rab_items', function (Blueprint $table) {
            $table->foreign('ahs_id')->references('id')->on('ahs')->onUpdate('cascade')->onDelete('cascade');
        });

        // 7. Drop the code column
        Schema::table('ahs', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
}
