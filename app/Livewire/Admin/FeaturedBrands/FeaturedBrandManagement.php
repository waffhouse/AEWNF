<?php

namespace App\Livewire\Admin\FeaturedBrands;

use App\Livewire\Admin\AdminComponent;
use App\Models\FeaturedBrand;
use App\Models\Inventory;
use Livewire\WithPagination;

class FeaturedBrandManagement extends AdminComponent
{
    use WithPagination;

    // For adding new brands
    public $showAddBrandForm = false;
    public $newBrandName = '';
    public $newDisplayOrder = 1;
    public $newIsActive = true;
    
    // For confirmation and editing
    public $brandToDelete = null;
    public $editingBrandId = null;
    public $editBrandName = '';
    public $editDisplayOrder = 1;
    public $editIsActive = true; // Boolean value for active status
    
    // Available brands for dropdown
    public $availableBrands = [];
    
    protected $rules = [
        'editBrandName' => 'required|string|max:255',
        'editDisplayOrder' => 'required|integer|min:1',
        'editIsActive' => 'required|in:0,1',
        'newBrandName' => 'required|string|max:255',
        'newDisplayOrder' => 'required|integer|min:1',
        'newIsActive' => 'required|boolean',
    ];

    protected $messages = [
        'editDisplayOrder.min' => 'The display order must be at least 1.',
        'newDisplayOrder.min' => 'The display order must be at least 1.',
    ];

    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard'];
    }

    protected function mountComponent(): void
    {
        $this->resetPage();
        $this->loadAvailableBrands();
    }
    
    /**
     * Load unique brands from inventory for the dropdown
     */
    private function loadAvailableBrands(): void
    {
        // Get all existing featured brand names
        $existingBrands = FeaturedBrand::pluck('brand')->toArray();
        
        // If we're editing, don't exclude the current brand being edited
        if ($this->editingBrandId) {
            $currentBrand = FeaturedBrand::find($this->editingBrandId);
            if ($currentBrand) {
                $existingBrands = array_diff($existingBrands, [$currentBrand->brand]);
            }
        }
        
        // Get all unique brands from inventory table and exclude ones already featured
        $this->availableBrands = Inventory::select('brand')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->whereNotIn('brand', $existingBrands)
            ->groupBy('brand')
            ->pluck('brand')
            ->toArray();
    }
    
    // Special handling for hydrating property values
    public function hydrate()
    {
        // Ensure editIsActive is properly cast to boolean
        // We intentionally don't cast here to ensure select values work correctly
        // The value will be cast to boolean when saving in the saveEdit method
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

    /**
     * Show the add brand form
     */
    public function showAddForm()
    {
        $this->showAddBrandForm = true;
        $this->newDisplayOrder = FeaturedBrand::count() + 1;
        $this->newIsActive = true;
        $this->loadAvailableBrands();
    }
    
    /**
     * Cancel adding a new brand
     */
    public function cancelAdd()
    {
        $this->reset(['showAddBrandForm', 'newBrandName', 'newDisplayOrder', 'newIsActive']);
    }
    
    /**
     * Add a new featured brand
     */
    public function addBrand()
    {
        $this->validate([
            'newBrandName' => 'required|string|max:255',
            'newDisplayOrder' => 'required|integer|min:1',
            'newIsActive' => 'required|boolean',
        ]);
        
        try {
            // Check if brand already exists
            $existingBrand = FeaturedBrand::where('brand', $this->newBrandName)->first();
            if ($existingBrand) {
                session()->flash('error', 'This brand is already featured.');
                return;
            }
            
            // Handle display order conflicts
            $existingOrder = FeaturedBrand::where('display_order', $this->newDisplayOrder)->first();
            if ($existingOrder) {
                // Make room for the new brand by incrementing orders >= newDisplayOrder
                FeaturedBrand::where('display_order', '>=', $this->newDisplayOrder)
                    ->increment('display_order');
            }
            
            // Create the new featured brand
            FeaturedBrand::create([
                'brand' => $this->newBrandName,
                'display_order' => $this->newDisplayOrder,
                'is_active' => $this->newIsActive,
                'created_by' => auth()->id(),
            ]);
            
            $this->reset(['showAddBrandForm', 'newBrandName', 'newDisplayOrder', 'newIsActive']);
            session()->flash('message', 'Brand added successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error adding brand: ' . $e->getMessage());
        }
    }

    /**
     * Toggle brand active status
     */
    public function toggleActive($id)
    {
        try {
            $brand = FeaturedBrand::findOrFail($id);
            $brand->update([
                'is_active' => !$brand->is_active,
            ]);
    
            session()->flash('message', 'Brand status updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating brand status: ' . $e->getMessage());
        }
    }

    /**
     * Confirm deletion of a brand
     */
    public function confirmDelete($id)
    {
        $this->brandToDelete = $id;
    }
    
    /**
     * Cancel brand deletion
     */
    public function cancelDelete()
    {
        $this->brandToDelete = null;
    }
    
    /**
     * Start editing a brand
     */
    public function startEdit($id)
    {
        try {
            $brand = FeaturedBrand::findOrFail($id);
            $this->editingBrandId = $id;
            $this->editBrandName = $brand->brand;
            $this->editDisplayOrder = $brand->display_order;
            // Convert boolean to string for the dropdown
            $this->editIsActive = $brand->is_active ? '1' : '0';
            
            // Load available brands for the dropdown
            $this->loadAvailableBrands();
        } catch (\Exception $e) {
            session()->flash('error', 'Error preparing edit: ' . $e->getMessage());
        }
    }
    
    /**
     * Cancel brand editing
     */
    public function cancelEdit()
    {
        $this->reset(['editingBrandId', 'editBrandName', 'editDisplayOrder', 'editIsActive']);
    }
    
    /**
     * Save edited brand
     */
    public function saveEdit()
    {
        if (!$this->editingBrandId) {
            session()->flash('error', 'No brand selected for editing.');
            return;
        }
        
        $this->validate([
            'editBrandName' => 'required|string|max:255',
            'editDisplayOrder' => 'required|integer|min:1',
            'editIsActive' => 'required|in:0,1',
        ]);
        
        try {
            $brand = FeaturedBrand::findOrFail($this->editingBrandId);
            $oldOrder = $brand->display_order;
            $newOrder = $this->editDisplayOrder;
            
            // Handle order change
            if ($oldOrder != $newOrder) {
                // Check if the new order already exists
                $existingBrand = FeaturedBrand::where('display_order', $newOrder)
                    ->where('id', '!=', $this->editingBrandId)
                    ->first();

                if ($existingBrand) {
                    // We have a conflict, so we need to shift brands
                    if ($newOrder > $oldOrder) {
                        // Moving down: shift all brands between old and new position up
                        FeaturedBrand::where('display_order', '>', $oldOrder)
                            ->where('display_order', '<=', $newOrder)
                            ->where('id', '!=', $this->editingBrandId)
                            ->decrement('display_order');
                    } else {
                        // Moving up: shift all brands between new and old position down
                        FeaturedBrand::where('display_order', '>=', $newOrder)
                            ->where('display_order', '<', $oldOrder)
                            ->where('id', '!=', $this->editingBrandId)
                            ->increment('display_order');
                    }
                }
            }
            
            // Convert select value to boolean
            // For select elements, "0" is falsy but we need to convert it explicitly
            $isActive = $this->editIsActive === "1";
            
            // Update brand
            $brand->update([
                'brand' => $this->editBrandName,
                'display_order' => $this->editDisplayOrder,
                'is_active' => $isActive,
            ]);
            
            $this->reset(['editingBrandId', 'editBrandName', 'editDisplayOrder', 'editIsActive']);
            session()->flash('message', 'Brand updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating brand: ' . $e->getMessage());
        }
    }

    /**
     * Delete a featured brand
     */
    public function deleteBrand($id = null)
    {
        $idToDelete = $id ?? $this->brandToDelete;
        
        if (!$idToDelete) {
            session()->flash('error', 'No brand selected for deletion.');
            return;
        }
        
        try {
            FeaturedBrand::destroy($idToDelete);
            $this->reorderBrands();
            $this->brandToDelete = null;
            
            session()->flash('message', 'Featured brand removed successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting brand: ' . $e->getMessage());
        }
    }

    /**
     * Reorders all brands to ensure sequential display_order values (1, 2, 3, etc.)
     */
    private function reorderBrands(): void
    {
        // Get all brands ordered by current display_order
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

    /**
     * Move a brand up in the order
     */
    public function moveUp($id)
    {
        try {
            $currentBrand = FeaturedBrand::findOrFail($id);
            $previousBrand = FeaturedBrand::where('display_order', '<', $currentBrand->display_order)
                ->orderBy('display_order', 'desc')
                ->first();
    
            if ($previousBrand) {
                $tempOrder = $previousBrand->display_order;
                $previousBrand->update(['display_order' => $currentBrand->display_order]);
                $currentBrand->update(['display_order' => $tempOrder]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error moving brand: ' . $e->getMessage());
        }
    }

    /**
     * Move a brand down in the order
     */
    public function moveDown($id)
    {
        try {
            $currentBrand = FeaturedBrand::findOrFail($id);
            $nextBrand = FeaturedBrand::where('display_order', '>', $currentBrand->display_order)
                ->orderBy('display_order', 'asc')
                ->first();
    
            if ($nextBrand) {
                $tempOrder = $nextBrand->display_order;
                $nextBrand->update(['display_order' => $currentBrand->display_order]);
                $currentBrand->update(['display_order' => $tempOrder]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error moving brand: ' . $e->getMessage());
        }
    }
}