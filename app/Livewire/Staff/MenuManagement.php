<?php

namespace App\Livewire\Staff;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

#[Layout('layouts::staff')]
#[Title('Menu Management')]
class MenuManagement extends Component
{
    use WithFileUploads; // Enables file upload handling for product images

    // Controls which tab is visible: 'products' or 'categories'
    public string $activeTab = 'products';

    // ─── Product Form State ──────────────────────────────────────────────────
    public bool $showProductModal = false;
    public ?int $editingProductId = null; // null = creating new, int = editing existing

    public string $productName = '';
    public int|string|null $productCategory = ''; // Category ID
    public string $productDescription = '';
    public string $productPrice = '';
    public string $productStock = '';
    public $productImage = null; // Livewire UploadedFile or null
    public bool $productAvailable = true;

    // ─── Category Form State ─────────────────────────────────────────────────
    public bool $showCategoryModal = false;
    public ?int $editingCategoryId = null; // null = creating new, int = editing existing

    public string $categoryName = '';
    public bool $categoryActive = true;

    // ─── Delete Confirmation State ───────────────────────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public string $deletingName = '';
    public string $deletingType = ''; // 'product' or 'category'

    // ═══════════════════════════════════════════════════════════════════════════
    // PRODUCT CRUD
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Opens the product modal in "create" mode with a blank form.
     */
    public function createProduct(): void
    {
        $this->resetProductForm();
        $this->showProductModal = true;
    }

    /**
     * Opens the product modal in "edit" mode with the existing product's data.
     */
    public function editProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingProductId = $product->id;
        $this->productName = $product->name;
        $this->productCategory = $product->category_id;
        $this->productDescription = $product->description ?? '';
        $this->productPrice = (string) $product->price;
        $this->productStock = (string) $product->stock;
        $this->productAvailable = $product->is_available;
        $this->productImage = null; // Don't pre-fill the image — staff can upload a new one
        $this->showProductModal = true;
    }

    /**
     * Saves the product (create or update depending on editingProductId).
     * Validates all fields, enforces unique product names, and uploads the image if provided.
     * Automatically marks a product as unavailable if stock is 0.
     */
    public function saveProduct(): void
    {
        // Build the unique rule — ignore the current product if editing
        $nameUnique = $this->editingProductId
            ? "unique:products,name,{$this->editingProductId}"
            : 'unique:products,name';

        $this->validate([
            'productName'        => ['required', 'string', 'max:255', $nameUnique],
            'productCategory'    => 'required|exists:categories,id',
            'productDescription' => 'nullable|string|max:500',
            'productPrice'       => 'required|numeric|min:1|max:99999',
            'productStock'       => 'required|integer|min:0',
            'productImage'       => 'nullable|image|max:2048', // Max 2MB
        ], [
            'productName.unique' => 'A product with this name already exists.',
        ], [
            // Custom attribute names for cleaner error messages
            'productName'        => 'product name',
            'productCategory'    => 'category',
            'productDescription' => 'description',
            'productPrice'       => 'price',
            'productStock'       => 'stock',
            'productImage'       => 'image',
        ]);

        // Check authorization via ProductPolicy
        if ($this->editingProductId) {
            $this->authorize('update', Product::class);
            $product = Product::findOrFail($this->editingProductId);
        } else {
            $this->authorize('create', Product::class);
        }

        $data = [
            'name'         => $this->productName,
            'slug'         => Str::slug($this->productName),
            'category_id'  => $this->productCategory,
            'description'  => $this->productDescription ?: null,
            'price'        => $this->productPrice,
            'stock'        => $this->productStock,
            // Force unavailable if stock is 0, otherwise respect the checkbox
            'is_available' => (int) $this->productStock > 0 ? $this->productAvailable : false,
        ];

        // Upload the image to storage/app/public/products if provided
        if ($this->productImage) {
            $data['image_path'] = $this->productImage->store('products', 'public');
        }

        if ($this->editingProductId) {
            $product->update($data);
            $message = 'Product updated successfully!';
        } else {
            // New products go to the end of the sort order
            $data['sort_order'] = Product::max('sort_order') + 1;
            Product::create($data);
            $message = 'Product created successfully!';
        }

        $this->showProductModal = false;
        $this->resetProductForm();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    /**
     * Toggles the is_available flag for a product.
     * Prevents marking a product as available if stock is 0.
     */
    public function toggleProductAvailability(int $id): void
    {
        $product = Product::findOrFail($id);

        if ($product->stock === 0) {
            $this->dispatch('toast', type: 'error', message: "Cannot mark {$product->name} as available — stock is 0.");
            return;
        }

        $product->update(['is_available' => !$product->is_available]);
        $status = $product->is_available ? 'available' : 'unavailable';
        $this->dispatch('toast', type: 'success', message: "{$product->name} marked as {$status}.");
    }

    /**
     * Opens the delete confirmation modal for a product or category.
     */
    public function confirmDelete(int $id, string $type): void
    {
        $this->deletingId = $id;
        $this->deletingType = $type;
        $this->deletingName = $type === 'product'
            ? Product::findOrFail($id)->name
            : Category::findOrFail($id)->name;
        $this->showDeleteModal = true;
    }

    /**
     * Executes the deletion after confirmation.
     */
    public function deleteConfirmed(): void
    {
        if ($this->deletingType === 'product') {
            $this->deleteProduct($this->deletingId);
        } else {
            $this->deleteCategory($this->deletingId);
        }

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
        $this->deletingType = '';
    }

    /**
     * Deletes a product. Enforces ProductPolicy::delete().
     */
    public function deleteProduct(int $id): void
    {
        $this->authorize('delete', Product::class);
        $product = Product::findOrFail($id);
        $name = $product->name;
        $product->delete();
        $this->dispatch('toast', type: 'success', message: "{$name} deleted.");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CATEGORY CRUD
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Opens the category modal in "create" mode with a blank form.
     */
    public function createCategory(): void
    {
        $this->resetCategoryForm();
        $this->showCategoryModal = true;
    }

    /**
     * Opens the category modal in "edit" mode with the existing category's data.
     */
    public function editCategory(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->editingCategoryId = $cat->id;
        $this->categoryName = $cat->name;
        $this->categoryActive = $cat->is_active;
        $this->showCategoryModal = true;
    }

    /**
     * Saves the category (create or update depending on editingCategoryId).
     * Validates the name and enforces uniqueness.
     */
    public function saveCategory(): void
    {
        $nameUnique = $this->editingCategoryId
            ? "unique:categories,name,{$this->editingCategoryId}"
            : 'unique:categories,name';

        $this->validate([
            'categoryName' => ['required', 'string', 'max:255', $nameUnique],
        ], [
            'categoryName.unique' => 'A category with this name already exists.',
        ]);

        $data = [
            'name' => $this->categoryName,
            'slug' => Str::slug($this->categoryName),
            'is_active' => $this->categoryActive,
        ];

        if ($this->editingCategoryId) {
            Category::findOrFail($this->editingCategoryId)->update($data);
            $message = 'Category updated successfully!';
        } else {
            // New categories go to the end of the sort order
            $data['sort_order'] = Category::max('sort_order') + 1;
            Category::create($data);
            $message = 'Category created successfully!';
        }

        $this->showCategoryModal = false;
        $this->resetCategoryForm();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    /**
     * Deletes a category if it has no products.
     * Prevents deletion if products still reference this category.
     */
    public function deleteCategory(int $id): void
    {
        $category = Category::findOrFail($id);

        if ($category->products()->count() > 0) {
            $this->dispatch('toast', type: 'error', message: 'Cannot delete a category that has products.');
            return;
        }

        $name = $category->name;
        $category->delete();
        $this->dispatch('toast', type: 'success', message: "{$name} deleted.");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FORM RESETS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Clears all product form fields and validation errors.
     */
    private function resetProductForm(): void
    {
        $this->editingProductId = null;
        $this->productName = '';
        $this->productCategory = '';
        $this->productDescription = '';
        $this->productPrice = '';
        $this->productStock = '';
        $this->productAvailable = true;
        $this->productImage = null;
        $this->resetValidation();
    }

    /**
     * Clears all category form fields and validation errors.
     */
    private function resetCategoryForm(): void
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryActive = true;
        $this->resetValidation();
    }

    /**
     * Loads all data needed to render the menu management page.
     * - products: all products with their category relationship
     * - categories: all categories with a count of how many products they have
     * - categoryOptions: active categories for the product form dropdown
     * - lowStockProducts: items with 5 or fewer units remaining (shown in the alert tab)
     */
    public function render()
    {
        return view('livewire.staff.menu-management', [
            'products' => Product::with('category')->orderBy('sort_order')->get(),
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
            'categoryOptions' => Category::active()->orderBy('name')->get(),
            'lowStockProducts' => Product::with('category')
                ->where('stock', '<=', 5)
                ->orderBy('stock')
                ->get(),
        ]);
    }
}
