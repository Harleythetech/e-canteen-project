<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

#[Layout('layouts::admin')]
#[Title('Menu Management')]
class MenuManagement extends Component
{
    use WithFileUploads;

    public string $activeTab = 'products';

    // Product form
    public bool $showProductModal = false;
    public ?int $editingProductId = null;

    #[Validate('required|string|max:255')]
    public string $productName = '';

    #[Validate('required|exists:categories,id')]
    public ?int $productCategory = null;

    #[Validate('nullable|string|max:500')]
    public string $productDescription = '';

    #[Validate('required|numeric|min:1|max:99999')]
    public float $productPrice = 0;

    #[Validate('required|integer|min:0')]
    public int $productStock = 0;

    #[Validate('nullable|image|max:2048')]
    public $productImage = null;

    public bool $productAvailable = true;

    // Category form
    public bool $showCategoryModal = false;
    public ?int $editingCategoryId = null;

    #[Validate('required|string|max:255', as: 'category name')]
    public string $categoryName = '';

    public bool $categoryActive = true;

    // Product CRUD

    public function createProduct(): void
    {
        $this->resetProductForm();
        $this->showProductModal = true;
    }

    public function editProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingProductId = $product->id;
        $this->productName = $product->name;
        $this->productCategory = $product->category_id;
        $this->productDescription = $product->description ?? '';
        $this->productPrice = (float) $product->price;
        $this->productStock = $product->stock;
        $this->productAvailable = $product->is_available;
        $this->productImage = null;
        $this->showProductModal = true;
    }

    public function saveProduct(): void
    {
        $this->authorize('create', Product::class);
        $this->validate();

        $data = [
            'name' => $this->productName,
            'slug' => Str::slug($this->productName),
            'category_id' => $this->productCategory,
            'description' => $this->productDescription ?: null,
            'price' => $this->productPrice,
            'stock' => $this->productStock,
            'is_available' => $this->productAvailable,
        ];

        if ($this->productImage) {
            $data['image_path'] = $this->productImage->store('products', 'public');
        }

        if ($this->editingProductId) {
            Product::findOrFail($this->editingProductId)->update($data);
        } else {
            Product::create($data);
        }

        $this->showProductModal = false;
        $this->resetProductForm();
    }

    public function toggleProductAvailability(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_available' => !$product->is_available]);
    }

    public function deleteProduct(int $id): void
    {
        $this->authorize('delete', Product::class);
        Product::findOrFail($id)->delete();
    }

    // Category CRUD

    public function createCategory(): void
    {
        $this->resetCategoryForm();
        $this->showCategoryModal = true;
    }

    public function editCategory(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->editingCategoryId = $cat->id;
        $this->categoryName = $cat->name;
        $this->categoryActive = $cat->is_active;
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryName' => 'required|string|max:255',
        ]);

        $data = [
            'name' => $this->categoryName,
            'slug' => Str::slug($this->categoryName),
            'is_active' => $this->categoryActive,
        ];

        if ($this->editingCategoryId) {
            Category::findOrFail($this->editingCategoryId)->update($data);
        } else {
            $data['sort_order'] = Category::max('sort_order') + 1;
            Category::create($data);
        }

        $this->showCategoryModal = false;
        $this->resetCategoryForm();
    }

    public function deleteCategory(int $id): void
    {
        $category = Category::findOrFail($id);

        if ($category->products()->count() > 0) {
            session()->flash('error', 'Cannot delete a category that has products.');
            return;
        }

        $category->delete();
    }

    // Form resets

    private function resetProductForm(): void
    {
        $this->editingProductId = null;
        $this->productName = '';
        $this->productCategory = null;
        $this->productDescription = '';
        $this->productPrice = 0;
        $this->productStock = 0;
        $this->productAvailable = true;
        $this->productImage = null;
        $this->resetValidation();
    }

    private function resetCategoryForm(): void
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.menu-management', [
            'products' => Product::with('category')->orderBy('sort_order')->get(),
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
            'categoryOptions' => Category::active()->orderBy('name')->get(),
        ]);
    }
}
