@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a 
        href="{{ route('admin.users.show', $user->id) }}"
        class="inline-flex items-center text-purple-600 hover:text-purple-700 font-medium">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to User Details
    </a>
</div>

<!-- Edit User Form -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-8">Edit User: {{ $user->name }}</h2>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input 
                    type="text" 
                    name="name" 
                    value="{{ old('name', $user->name) }}"
                    required
                    class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input 
                    type="email" 
                    name="email" 
                    value="{{ old('email', $user->email) }}"
                    required
                    class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone Number -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                <input 
                    type="text" 
                    name="phone_number" 
                    value="{{ old('phone_number', $user->phone_number) }}"
                    placeholder="0912345678"
                    required
                    class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition @error('phone_number') border-red-500 @enderror">
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Format: 10 digits starting with 09 (e.g., 0912345678)</p>
            </div>

            <!-- Region -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Region *</label>
                <input 
                    type="text" 
                    name="region" 
                    value="{{ old('region', $user->region) }}"
                    placeholder="Tripoli"
                    required
                    class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition @error('region') border-red-500 @enderror">
                @error('region')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                <select 
                    name="role"
                    required
                    {{ !$canEditRole ? 'disabled' : '' }}
                    class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition @error('role') border-red-500 @enderror {{ !$canEditRole ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                    <option value="">Select Role</option>
                    <option value="tenant" {{ old('role', $user->role) === 'tenant' ? 'selected' : '' }}>Tenant</option>
                    <option value="owner" {{ old('role', $user->role) === 'owner' ? 'selected' : '' }}>Owner</option>
                    @if($canEditRole)
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    @endif
                </select>
                @if(!$canEditRole)
                    <input type="hidden" name="role" value="{{ $user->role }}">
                    <p class="mt-1 text-xs text-gray-500">Only super admin can change admin roles</p>
                @endif
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="flex items-center h-full">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            <!-- Password (Optional) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Leave blank to keep current password</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-4 mt-8">
            <button 
                type="submit"
                class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
                Update User
            </button>
            <a 
                href="{{ route('admin.users.show', $user->id) }}"
                class="flex-1 text-center border-2 border-gray-300 text-gray-700 py-3 px-6 rounded-xl font-semibold hover:bg-gray-50 transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

