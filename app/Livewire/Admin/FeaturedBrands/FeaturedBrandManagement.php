<?php

namespace App\Livewire\Admin\FeaturedBrands;

use App\Livewire\Admin\AdminComponent;
use App\Models\FeaturedBrand;
use App\Models\Inventory;
use Livewire\WithPagination;

class FeaturedBrandManagement extends AdminComponent
{
    use WithPagination;

    public $showAddBrandModal = false;

    public $showEditBrandModal = false;

    public $showDeleteModal = false;

    public $brandToAdd = '';

    public $displayOrder = 1;

    public $brandId = null;

    public $currentBrand = null;

    // Use the searchQuery from parent class
    public $availableBrands = [];

    protected $listeners = ['refreshBrands' => '$refresh'];

    protected $rules = [
        'brandToAdd' => 'required|string|max:255',
        'displayOrder' => 'required|integer|min:1',
    ];

    protected $messages = [
        'displayOrder.min' => 'The display order must be at least 1.',
    ];

    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard'];
    }

    protected function mountComponent(): void
    {
        $this->resetPage();
        $this->currentBrand = null;
    }

    public function render()
    {
        $featuredBrands = FeaturedBrand::query()
            ->with('creator')
            ->ordered()
            ->paginate(10);

        return view('livewire.admin.featured-brands.featured-brand-management', [
            'featuredBrands' => $featuredBrands,
        ]);
    }

    public function openAddModal()
    {
        $this->resetValidation();
        $this->reset(['brandToAdd', 'displayOrder']);

        // Count total brands and set the new one to be the next in sequence
        $count = FeaturedBrand::count();
        $this->displayOrder = $count + 1;

        // Load all available brands for dropdown (excluding already featured brands)
        $this->loadAvailableBrands();

        $this->showAddBrandModal = true;
    }

    /**
     * Load available brands for dropdown
     */
    private function loadAvailableBrands()
    {
        // Get brands that are already featured
        $featuredBrands = FeaturedBrand::pluck('brand')->toArray();

        // Get all unique brands from inventory, excluding already featured ones
        $this->availableBrands = Inventory::select('brand')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->whereNotIn('brand', $featuredBrands)
            ->groupBy('brand')
            ->orderBy('brand')
            ->pluck('brand', 'brand')
            ->toArray();
    }

    // Brand search functionality removed in favor of dropdown

    public function addBrand()
    {
        $this->validate();

        FeaturedBrand::create([
            'brand' => $this->brandToAdd,
            'display_order' => $this->displayOrder,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        $this->showAddBrandModal = false;
        $this->reset(['brandToAdd', 'displayOrder']);

        $this->dispatch('close-modal', 'add-brand-modal');
        session()->flash('message', 'Brand added to featured brands successfully.');
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $this->brandId = $id;
        $this->currentBrand = FeaturedBrand::findOrFail($id);
        $this->brandToAdd = $this->currentBrand->brand;
        $this->displayOrder = $this->currentBrand->display_order;

        // We'll load all brands plus the current one for edit
        $this->loadAvailableBrandsForEdit();

        $this->showEditBrandModal = true;
    }

    /**
     * Load available brands for edit modal dropdown
     * (includes the current brand that's being edited)
     */
    private function loadAvailableBrandsForEdit()
    {
        // Get brands that are already featured (excluding the current one)
        $featuredBrands = FeaturedBrand::where('id', '!=', $this->brandId)
            ->pluck('brand')
            ->toArray();

        // Get all unique brands from inventory, excluding already featured ones
        $this->availableBrands = Inventory::select('brand')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->where(function ($query) use ($featuredBrands) {
                $query->whereNotIn('brand', $featuredBrands)
                    ->orWhere('brand', $this->brandToAdd);
            })
            ->groupBy('brand')
            ->orderBy('brand')
            ->pluck('brand', 'brand')
            ->toArray();
    }

    public function updateBrand()
    {
        $this->validate();

        if ($this->currentBrand) {
            $oldOrder = $this->currentBrand->display_order;
            $newOrder = $this->displayOrder;

            // If the display order has changed
            if ($oldOrder != $newOrder) {
                // Check if the new order already exists
                $existingBrand = FeaturedBrand::where('display_order', $newOrder)
                    ->where('id', '!=', $this->currentBrand->id)
                    ->first();

                if ($existingBrand) {
                    // We have a conflict, so we need to shift brands
                    if ($newOrder > $oldOrder) {
                        // Moving down: shift all brands between old and new position up
                        FeaturedBrand::where('display_order', '>', $oldOrder)
                            ->where('display_order', '<=', $newOrder)
                            ->where('id', '!=', $this->currentBrand->id)
                            ->decrement('display_order');
                    } else {
                        // Moving up: shift all brands between new and old position down
                        FeaturedBrand::where('display_order', '>=', $newOrder)
                            ->where('display_order', '<', $oldOrder)
                            ->where('id', '!=', $this->currentBrand->id)
                            ->increment('display_order');
                    }
                }
            }

            // Update the brand with new values
            $this->currentBrand->update([
                'brand' => $this->brandToAdd,
                'display_order' => $newOrder,
            ]);

            $this->showEditBrandModal = false;
            $this->reset(['brandId', 'currentBrand', 'brandToAdd', 'displayOrder']);

            $this->dispatch('close-modal', 'edit-brand-modal');
            session()->flash('message', 'Featured brand updated successfully.');
        } else {
            session()->flash('error', 'Error updating brand: brand not found.');
        }
    }

    public function toggleActive($id)
    {
        $brand = FeaturedBrand::findOrFail($id);
        $brand->update([
            'is_active' => ! $brand->is_active,
        ]);

        session()->flash('message', 'Brand status updated successfully.');
    }

    public function confirmDelete($id)
    {
        $this->brandId = $id;
        $this->currentBrand = FeaturedBrand::findOrFail($id);
        $this->showDeleteModal = true;
    }
    
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->reset(['brandId', 'currentBrand']);
    }

    public function deleteBrand()
    {
        if ($this->brandId) {
            // Get the display order of the brand to be deleted
            $brandToDelete = FeaturedBrand::findOrFail($this->brandId);
            $deletedOrder = $brandToDelete->display_order;

            // Delete the brand
            FeaturedBrand::destroy($this->brandId);

            // Reorder all remaining brands to ensure sequential ordering
            $this->reorderBrands();

            $this->showDeleteModal = false;
            $this->reset(['brandId', 'currentBrand']);

            $this->dispatch('close-modal', 'delete-brand-modal');
            session()->flash('message', 'Featured brand removed successfully.');
        }
    }

    /**
     * Reorders all brands to ensure sequential display_order values (1, 2, 3, etc.)
     */
    private function reorderBrands(): void
    {
        // Get all active brands ordered by current display_order
        $brands = FeaturedBrand::orderBy('display_order')->get();

        // Reassign display_order sequentially starting from 1
        foreach ($brands as $index => $brand) {
            $newOrder = $index + 1;

            // Only update if the order has changed
            if ($brand->display_order !== $newOrder) {
                $brand->update(['display_order' => $newOrder]);
            }
        }
    }

    public function moveUp($id)
    {
        $currentBrand = FeaturedBrand::findOrFail($id);
        $previousBrand = FeaturedBrand::where('display_order', '<', $currentBrand->display_order)
            ->orderBy('display_order', 'desc')
            ->first();

        if ($previousBrand) {
            $tempOrder = $previousBrand->display_order;
            $previousBrand->update(['display_order' => $currentBrand->display_order]);
            $currentBrand->update(['display_order' => $tempOrder]);
        }
    }

    public function moveDown($id)
    {
        $currentBrand = FeaturedBrand::findOrFail($id);
        $nextBrand = FeaturedBrand::where('display_order', '>', $currentBrand->display_order)
            ->orderBy('display_order', 'asc')
            ->first();

        if ($nextBrand) {
            $tempOrder = $nextBrand->display_order;
            $nextBrand->update(['display_order' => $currentBrand->display_order]);
            $currentBrand->update(['display_order' => $tempOrder]);
        }
    }
}
