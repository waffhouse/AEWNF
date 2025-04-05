<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Hero Banner with Red Gradient Background -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 sm:rounded-lg shadow-md">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold">Profile Settings</h2>
                        <p class="text-sm text-red-100 mt-1">Manage your account information and preferences.</p>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
