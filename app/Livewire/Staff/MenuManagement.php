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
    use WithFileUploads;

    public string $activeTab = 'products';

    // Product form
    public bool $showProductModal = false;
    public ?int $editingProductId = null;

    public string $productName = '';
    public int|string|null $productCategory = '';
    public string $productDescription = '';
    public string $productPrice = '';
    public string $productStock = '';
    public $productImage = null;
    public bool $productAvailable = true;

    // Category form
    public bool $showCategoryModal = false;
    public ?int $editingCategoryId = null;

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
        $this->productPrice = (string) $product->price;
        $this->productStock = (string) $product->stock;
        $this->productAvailable = $product->is_available;
        $this->productImage = null;
        $this->showProductModal = true;
    }

    public function saveProduct(): void
    {
        $nameUnique = $this->editingProductId
            ? "unique:products,name,{$this->editingProductId}"
            : 'unique:products,name';

        $this->validate([
            'productName'        => ['required', 'string', 'max:255', $nameUnique],
            'productCategory'    => 'required|exists:categories,id',
            'productDescription' => 'nullable|string|max:500',
            'productPrice'       => 'required|numeric|min:1|max:99999',
            'productStock'       => 'required|integer|min:0',
            'productImage'       => 'nullable|image|max:2048',
        ], [
            'productName.unique' => 'A product with this name already exists.',
        ], [
            'productName'        => 'product name',
            'productCategory'    => 'category',
            'productDescription' => 'description',
            'productPrice'       => 'price',
            'productStock'       => 'stock',
            'productImage'       => 'image',
        ]);

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
            'is_available' => $this->productAvailable,
        ];

        if ($this->productImage) {
            $data['image_path'] = $this->productImage->store('products', 'public');
        }

        if ($this->editingProductId) {
            $product->update($data);
            $message = 'Product updated successfully!';
        } else {
            $data['sort_order'] = Product::max('sort_order') + 1;
            Product::create($data);
            $message = 'Product created successfully!';
        }

        $this->showProductModal = false;
        $this->resetProductForm();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function toggleProductAvailability(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_available' => !$product->is_available]);
        $status = $product->is_available ? 'available' : 'unavailable';
        $this->dispatch('toast', type: 'success', message: "{$product->name} marked as {$status}.");
    }

    public function deleteProduct(int $id): void
    {
        $this->authorize('delete', Product::class);
        $product = Product::findOrFail($id);
        $name = $product->name;
        $product->delete();
        $this->dispatch('toast', type: 'success', message: "{$name} deleted.");
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
            $data['sort_order'] = Category::max('sort_order') + 1;
            Category::create($data);
            $message = 'Category created successfully!';
        }

        $this->showCategoryModal = false;
        $this->resetCategoryForm();
        $this->dispatch('toast', type: 'success', message: $message);
    }

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

    // Form resets

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

    private function resetCategoryForm(): void
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.staff.menu-management', [
            'products' => Product::with('category')->orderBy('sort_order')->get(),
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
            'categoryOptions' => Category::active()->orderBy('name')->get(),
        ]);
    }
}
