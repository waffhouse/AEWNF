<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Trait FormValidatable
 *
 * Provides standardized form validation and error handling functionality
 * for Livewire components that manage forms. Centralizes validation logic
 * and error handling patterns.
 */
trait FormValidatable
{
    /**
     * Indicates whether the form is in create or update mode
     */
    public bool $isEditMode = false;

    /**
     * The ID of the record being edited (when in edit mode)
     *
     * @var mixed
     */
    public $editId = null;

    /**
     * Form validation errors
     *
     * @var array
     */
    public $formErrors = [];

    /**
     * Define base validation rules that apply to all scenarios
     */
    abstract protected function baseRules(): array;

    /**
     * Define rules that only apply when creating a new record
     */
    protected function createRules(): array
    {
        return [];
    }

    /**
     * Define rules that only apply when updating an existing record
     */
    protected function updateRules(): array
    {
        return [];
    }

    /**
     * Define custom error messages for validation rules
     */
    protected function customMessages(): array
    {
        return [];
    }

    /**
     * Get the appropriate validation rules based on form mode
     */
    public function rules(): array
    {
        $baseRules = $this->baseRules();

        if ($this->isEditMode) {
            return array_merge($baseRules, $this->updateRules());
        } else {
            return array_merge($baseRules, $this->createRules());
        }
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return $this->customMessages();
    }

    /**
     * Set component to create mode
     */
    public function enterCreateMode(): void
    {
        $this->isEditMode = false;
        $this->editId = null;
        $this->formErrors = [];
        $this->resetForm();
    }

    /**
     * Set component to edit mode with specified record ID
     *
     * @param  mixed  $id
     */
    public function enterEditMode($id): void
    {
        $this->isEditMode = true;
        $this->editId = $id;
        $this->formErrors = [];
        $this->loadRecord($id);
    }

    /**
     * Reset the form to its default state
     *
     * Child components should override this to reset their specific properties
     */
    public function resetForm(): void
    {
        $this->formErrors = [];
        $this->isEditMode = false;
        $this->editId = null;
    }

    /**
     * Load a record for editing
     *
     * Child components must implement this to load specific model data
     *
     * @param  mixed  $id
     */
    abstract protected function loadRecord($id): void;

    /**
     * Validate form data with standardized error handling
     *
     * @param  array|null  $rules  Custom rules to use instead of the default rules
     * @param  array|null  $messages  Custom messages to use instead of the default messages
     * @return bool Whether validation passed
     */
    protected function validateForm(?array $rules = null, ?array $messages = null): bool
    {
        $this->formErrors = [];

        try {
            $rules = $rules ?? $this->rules();
            $messages = $messages ?? $this->messages();

            // Perform validation
            $validatedData = $this->validate($rules, $messages);

            return true;
        } catch (ValidationException $e) {
            $this->formErrors = $e->validator->errors()->toArray();

            // Log validation errors in development/staging environments
            if (! app()->environment('production')) {
                Log::debug('Validation failed: '.json_encode($this->formErrors));
            }

            $this->dispatch('validation-failed', $this->formErrors);

            return false;
        } catch (\Exception $e) {
            Log::error('Unexpected error during validation: '.$e->getMessage());

            // Add a generic error
            $this->formErrors['general'] = ['An unexpected error occurred during validation.'];
            $this->dispatch('validation-failed', $this->formErrors);

            return false;
        }
    }

    /**
     * Save form data with standardized error handling
     *
     * @param  callable  $saveFunction  Function that performs the actual save operation
     * @param  string  $successMessage  Message to display on success
     * @param  string  $errorPrefix  Prefix for error messages
     * @return bool Whether the save operation succeeded
     */
    protected function saveWithErrorHandling(callable $saveFunction, string $successMessage, string $errorPrefix = 'Error'): bool
    {
        try {
            // Execute the save function passed in
            $result = $saveFunction();

            // Flash success message
            session()->flash('message', $successMessage);
            $this->dispatch('message', $successMessage);

            return true;
        } catch (ValidationException $e) {
            $this->formErrors = $e->validator->errors()->toArray();
            Log::error($errorPrefix.' validation failed: '.json_encode($this->formErrors));
            $this->dispatch('validation-failed', $this->formErrors);

            return false;
        } catch (\Exception $e) {
            Log::error($errorPrefix.': '.$e->getMessage());
            session()->flash('error', $errorPrefix.': '.$e->getMessage());
            $this->dispatch('error', $errorPrefix.': '.$e->getMessage());

            return false;
        }
    }

    /**
     * Helper method to get specific form error message
     *
     * @param  string  $field  The form field to get errors for
     * @return string|null First error message for field or null
     */
    public function getFormError(string $field): ?string
    {
        return isset($this->formErrors[$field]) ? $this->formErrors[$field][0] : null;
    }

    /**
     * Check if a field has validation errors
     *
     * @param  string  $field  The form field to check
     * @return bool Whether the field has errors
     */
    public function hasFormError(string $field): bool
    {
        return isset($this->formErrors[$field]);
    }

    /**
     * Get all validation errors
     *
     * @return array All validation errors
     */
    public function getAllFormErrors(): array
    {
        return $this->formErrors;
    }
}
