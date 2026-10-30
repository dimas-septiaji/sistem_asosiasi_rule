<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. datasets
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('original_filename');
            $table->string('storage_path')->nullable();
            $table->string('file_type', 20)->default('csv');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('total_transactions')->default(0);
            $table->unsignedInteger('total_items')->default(0);
            $table->string('status', 30)->default('imported'); // uploaded, processing, imported, failed
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'status']);
        });

        // 2. transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->string('transaction_code');
            $table->timestamp('transaction_at')->nullable();
            $table->timestamps();

            $table->unique(['dataset_id', 'transaction_code']);
            $table->index(['dataset_id', 'transaction_at']);
        });

        // 3. items
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->string('name');
            $table->string('normalized_name');
            $table->timestamps();

            $table->unique(['dataset_id', 'normalized_name']);
            $table->index(['dataset_id', 'name']);
        });

        // 4. transaction_items
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->timestamps();

            $table->unique(['transaction_id', 'item_id']);
            $table->index(['item_id', 'transaction_id']);
        });

        // 5. analyses
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('algorithm', 30)->default('apriori');
            $table->decimal('min_support', 8, 4); // stored as ratio 0.0000 - 1.0000
            $table->decimal('min_confidence', 8, 4); // stored as ratio 0.0000 - 1.0000
            $table->decimal('min_lift', 8, 4)->nullable();
            $table->string('status', 30)->default('pending'); // pending, processing, completed, failed
            $table->unsignedInteger('total_transactions')->default(0);
            $table->unsignedInteger('total_frequent_itemsets')->default(0);
            $table->unsignedInteger('total_rules')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['dataset_id', 'status']);
            $table->index('created_at');
        });

        // 6. itemsets
        Schema::create('itemsets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->constrained('analyses')->cascadeOnDelete();
            $table->unsignedSmallInteger('itemset_size'); // 1, 2, 3, etc.
            $table->string('itemset_hash', 64); // hash of sorted item IDs for fast deduplication & lookup
            $table->unsignedInteger('support_count');
            $table->decimal('support', 10, 6); // ratio between 0 and 1
            $table->timestamps();

            $table->unique(['analysis_id', 'itemset_hash']);
            $table->index(['analysis_id', 'itemset_size']);
            $table->index(['analysis_id', 'support']);
        });

        // 7. itemset_items
        Schema::create('itemset_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itemset_id')->constrained('itemsets')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();

            $table->unique(['itemset_id', 'item_id']);
        });

        // 8. association_rules
        Schema::create('association_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->constrained('analyses')->cascadeOnDelete();
            $table->foreignId('antecedent_itemset_id')->constrained('itemsets')->cascadeOnDelete();
            $table->foreignId('consequent_itemset_id')->constrained('itemsets')->cascadeOnDelete();
            $table->foreignId('union_itemset_id')->constrained('itemsets')->cascadeOnDelete();
            $table->unsignedInteger('support_count');
            $table->decimal('support', 10, 6);
            $table->decimal('confidence', 10, 6);
            $table->decimal('lift', 12, 6);
            $table->timestamps();

            $table->unique(['analysis_id', 'antecedent_itemset_id', 'consequent_itemset_id'], 'unique_analysis_rule');
            $table->index(['analysis_id', 'confidence']);
            $table->index(['analysis_id', 'lift']);
            $table->index(['analysis_id', 'support']);
        });

        // 9. apriori_iterations
        Schema::create('apriori_iterations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->constrained('analyses')->cascadeOnDelete();
            $table->unsignedSmallInteger('k'); // iteration 1, 2, 3...
            $table->unsignedInteger('candidate_count')->default(0);
            $table->unsignedInteger('frequent_count')->default(0);
            $table->unsignedBigInteger('duration_ms')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['analysis_id', 'k']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apriori_iterations');
        Schema::dropIfExists('association_rules');
        Schema::dropIfExists('itemset_items');
        Schema::dropIfExists('itemsets');
        Schema::dropIfExists('analyses');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('datasets');
    }
};
