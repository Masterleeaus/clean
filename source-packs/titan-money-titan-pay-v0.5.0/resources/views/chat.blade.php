@extends('layouts.app')

@section('title', 'Chat')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji-picker-element@1.21.0/picker.css">
@endpush
@section('content')
    <div class="flex h-screen">

        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="sidebar-overlay"></div>

        <!-- Left Sidebar -->
        <div class="w-96 bg-white border-r border-gray-200 flex flex-col left-sidebar" id="left-sidebar">

            <!-- User Profile Section -->
            <div class="p-6 pb-0 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            @php
                                $avatarUrl = auth()->user()->avatar
                                    ? asset('storage/' . auth()->user()->avatar)
                                    : 'https://ui-avatars.com/api/?name=' .
                                        urlencode(auth()->user()->name) .
                                        '&background=724FF9&color=fff';
                            @endphp
                            <img src="{{ $avatarUrl }}" alt="User"
                                class="w-12 h-12 rounded-full ring-2 ring-secondary object-cover" id="current-user-avatar">
                            <span
                                class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900" id="current-user-name">{{ auth()->user()->name }}</h3>
                            <span class="text-xs text-green-600 font-medium">available</span>
                        </div>
                    </div>
                    <div class="relative">
                        <a href="{{ route('settings') }}" class="text-gray-400 hover:text-gray-600 transition">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="relative">
                    <input type="text" id="search-conversations" placeholder="Search"
                        class="w-full pl-10 pr-4 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-secondary transition">
                    <i data-lucide="search"
                        class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>

                <!-- Online Users List -->
                <div class="py-3">
                    <div id="online-users-list" class="flex space-x-3 overflow-x-auto scrollbar-hide">
                        <!-- Online users will be loaded here dynamically -->
                    </div>
                </div>
            </div>

            <!-- Navigation Icons -->
            <div class="flex items-center justify-around py-4 border-b border-gray-200">
                <button id="nav-chats-btn" class="nav-icon active flex justify-center items-center" title="All Chats">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                </button>
                <button id="nav-users-btn" class="nav-icon flex justify-center items-center" title="Groups">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </button>
                <button id="new-chat-btn" class="nav-icon flex justify-center items-center" title="New Chat">
                    <i data-lucide="message-square-plus" class="w-5 h-5"></i>
                </button>
                <button id="create-group-btn" class="nav-icon flex justify-center items-center" title="Create Group">
                    <i data-lucide="users-round" class="w-5 h-5"></i>
                </button>
                <button id="nav-notifications-btn" class="nav-icon flex justify-center items-center relative"
                    title="Notifications">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span id="notification-badge"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center hidden">0</span>
                </button>
            </div>

            <!-- Chat List -->
            <div class="flex-1 overflow-y-auto">
                <div class="px-4 py-3 flex items-center justify-between">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase">Chats</h4>
                </div>

                <!-- Chat Items Container -->
                <div id="conversations-list">
                    <!-- Conversations will be loaded here dynamically -->
                </div>
            </div>
        </div>

        <!-- Center Chat Area -->
        <div class="flex-1 flex flex-col bg-gray-50 overflow-hidden">

            <!-- Chat Header -->
            <div class="bg-white border-b border-gray-200 p-4 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1">
                        <button id="left-sidebar-toggle" class="lg:hidden text-gray-600 hover:text-gray-900">
                            <i data-lucide="menu" class="w-6 h-6"></i>
                        </button>
                        <div id="chat-header-content" class="flex items-center space-x-1">
                            <h2 class="text-xl font-semibold text-gray-900" id="chat-header-title">Select a conversation
                            </h2>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button id="ai-summary-btn"
                            class="hidden p-1 text-xs text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition"
                            title="AI Summary">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </button>
                        <button id="ai-suggestions-btn"
                            class="hidden p-1 text-xs text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition"
                            title="AI Reply Suggestions">
                            <i data-lucide="lightbulb" class="w-5 h-5"></i>
                        </button>
                        <button id="toggle-chat-info"
                            class="hidden lg:block p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition"
                            title="Toggle Chat Info">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </button>
                        <button id="right-sidebar-toggle" class="lg:hidden text-gray-600 hover:text-gray-900">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4">
                <div class="text-center text-gray-500 mt-20">
                    <i data-lucide="message-circle" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                    <p>Select a conversation to start chatting</p>
                </div>
            </div>

            <!-- Typing Indicator -->
            <div id="typing-indicator" class="px-6 py-2 hidden flex-shrink-0">
                <div class="flex items-center space-x-2 bg-white rounded-lg p-2 w-fit">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                    <span class="text-xs text-gray-500 font-medium" id="typing-text">typing...</span>
                </div>
            </div>

            <!-- Message Input Area -->
            <div class="bg-white border-t border-gray-200 p-4 flex-shrink-0">
                <!-- AI Suggestions Chips -->
                <div id="ai-suggestions-container" class="hidden mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-purple-600">AI Suggestions:</span>
                        <button id="close-suggestions" class="text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div id="suggestions-chips" class="flex flex-wrap gap-2">
                        <!-- Suggestion chips will be inserted here -->
                    </div>
                </div>

                <!-- File Preview Area -->
                <div id="file-preview-container" class="mb-3 hidden">
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <i data-lucide="file" class="w-8 h-8 text-secondary flex-shrink-0"></i>
                        <div class="flex-1 min-w-0">
                            <p id="file-preview-name" class="text-sm font-medium text-gray-900 truncate"></p>
                            <p id="file-preview-size" class="text-xs text-gray-500"></p>
                        </div>
                        <button id="remove-file-btn" class="text-gray-400 hover:text-red-500 transition flex-shrink-0">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <form id="message-form" class="flex items-end space-x-2">
                    <div class="relative flex-1">
                        <textarea id="message-input" placeholder="Write your message..."
                            class="w-full px-4 py-3 pl-20 sm:pl-24 bg-gray-50 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition resize-none overflow-y-auto"
                            rows="1"></textarea>

                        <div class="absolute left-2 sm:left-3 bottom-2.5 flex items-center space-x-1">
                            <button class="text-gray-400 hover:text-gray-600 transition min-w-6 sm:min-w-8"
                                id="attachment-btn" type="button">
                                <i data-lucide="paperclip" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </button>
                            <button class="text-gray-400 hover:text-gray-600 transition min-w-6 sm:min-w-8" id="emoji-btn"
                                type="button">
                                <i data-lucide="smile" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </button>
                        </div>
                    </div>

                    <input type="file" id="attachment-input" class="hidden"
                        accept="image/*,.pdf,.doc,.docx,.txt,.zip">

                    <button id="send-btn" type="button"
                        class="bg-gradient-to-r from-primary to-secondary text-white p-3 rounded-lg transition shadow-lg hover:shadow-xl transform hover:scale-105 flex-shrink-0">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="w-80 bg-white border-l border-gray-200 flex flex-col overflow-auto transition-all duration-300 ease-in-out right-sidebar"
            id="chat-info-sidebar">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Chat Info</h3>
                <div id="chat-info-content" class="text-center text-gray-500">
                    Select a conversation to view details
                </div>
            </div>
        </div>

    </div>

    <!-- Create Group Modal -->
    <div id="create-group-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Create New Group</h3>
                <button class="modal-close text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Group Name Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Group Name</label>
                <input type="text" id="group-name-input" placeholder="Enter group name..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition">
            </div>

            <!-- User Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Select Members</label>
                <div id="group-users-list"
                    class="max-h-64 overflow-y-auto space-y-2 border border-gray-200 rounded-lg p-3">
                    <!-- Users will be loaded here -->
                </div>
                <p class="text-xs text-gray-500 mt-2"><span id="selected-count">0</span> members selected</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button
                    class="modal-close flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    Cancel
                </button>
                <button id="create-group-submit"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:from-secondary hover:to-secondary-dark transition shadow-lg hover:shadow-xl font-medium">
                    Create Group
                </button>
            </div>
        </div>
    </div>



    <!-- New Chat Modal -->
    <div id="new-chat-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Start New Chat</h3>
                <button class="modal-close text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Search Users -->
            <div class="mb-4">
                <div class="relative">
                    <input type="text" id="user-search-input" placeholder="Search users..."
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition">
                    <i data-lucide="search"
                        class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- User List -->
            <div id="users-list" class="max-h-96 overflow-y-auto space-y-2">
                <!-- Users will be loaded here -->
            </div>
        </div>
    </div>

    <!-- File Category Modal -->
    <div id="file-category-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content max-w-3xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900" id="file-category-title">Files</h3>
                <button class="modal-close text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Files Grid -->
            <div id="file-category-content" class="max-h-96 overflow-y-auto">
                <!-- Files will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Generic Confirmation Modal -->
    <div id="confirmation-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h3 id="confirmation-title" class="text-xl font-semibold text-gray-900">Confirmation</h3>
                <button class="modal-close text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="mb-6">
                <div class="flex items-center space-x-3 p-4 rounded-lg border" id="confirmation-alert-box">
                    <i id="confirmation-icon" data-lucide="alert-triangle" class="w-8 h-8 flex-shrink-0"></i>
                    <div>
                        <p id="confirmation-message" class="text-sm font-medium text-gray-900"></p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button id="confirmation-cancel-btn"
                    class="modal-close flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    Cancel
                </button>
                <button id="confirmation-confirm-btn"
                    class="flex-1 px-4 py-3 text-white rounded-lg transition shadow-lg hover:shadow-xl font-medium">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Group Modal -->
    <div id="edit-group-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Edit Group</h3>
                <button class="modal-close text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Group Name Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Group Name</label>
                <input type="text" id="edit-group-name" placeholder="Enter group name..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition">
            </div>

            <!-- Group Avatar Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Group Avatar</label>
                <div class="flex items-center space-x-4">
                    <img id="edit-group-avatar-preview" src="" alt="Group Avatar"
                        class="w-16 h-16 rounded-full object-cover border border-gray-200">
                    <label
                        class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">
                        Change Photo
                        <input type="file" id="edit-group-avatar" class="hidden" accept="image/*">
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button
                    class="modal-close flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    Cancel
                </button>
                <button id="update-group-btn"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:from-secondary hover:to-secondary-dark transition shadow-lg hover:shadow-xl font-medium">
                    Update Group
                </button>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div id="add-member-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Add Members</h3>
                <button class="modal-close text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- User Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Select Users</label>
                <div id="add-member-list"
                    class="max-h-64 overflow-y-auto space-y-2 border border-gray-200 rounded-lg p-3">
                    <!-- Users will be loaded here -->
                </div>
                <p class="text-xs text-gray-500 mt-2"><span id="add-member-count">0</span> users selected</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button
                    class="modal-close flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    Cancel
                </button>
                <button id="add-member-submit"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:from-secondary hover:to-secondary-dark transition shadow-lg hover:shadow-xl font-medium">
                    Add Members
                </button>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="image-preview-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content max-w-4xl p-0 bg-transparent shadow-none">
            <div class="relative">
                <!-- Close Button -->
                <button
                    class="modal-close absolute top-4 right-4 z-10 w-10 h-10 bg-black bg-opacity-50 hover:bg-opacity-70 rounded-full flex items-center justify-center text-white transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>

                <!-- Download Button -->
                <a id="image-download-btn" href="" download=""
                    class="absolute top-4 left-4 z-10 w-10 h-10 bg-black bg-opacity-50 hover:bg-opacity-70 rounded-full flex items-center justify-center text-white transition">
                    <i data-lucide="download" class="w-5 h-5"></i>
                </a>

                <!-- Image Container -->
                <div class="bg-black rounded-lg overflow-hidden">
                    <img id="preview-image" src="" alt="Preview"
                        class="w-full h-auto max-h-[80vh] object-contain">
                </div>

                <!-- Image Name -->
                <div class="mt-3 text-center">
                    <p id="preview-image-name"
                        class="text-white text-sm font-medium bg-black bg-opacity-50 inline-block px-4 py-2 rounded-lg">
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Summary Modal -->
    <div id="ai-summary-modal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="modal-content bg-white rounded-xl p-8 max-w-2xl w-11/12 max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i data-lucide="sparkles" class="w-6 h-6 text-purple-600 mr-2"></i>
                    AI Chat Summary
                </h3>
                <button class="modal-close text-gray-400 hover:text-gray-600 transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div id="summary-content" class="prose max-w-none">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button class="modal-close px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>

    <!-- Emoji Picker -->
    <script type="module">
        import {
            Picker
        } from 'https://cdn.jsdelivr.net/npm/emoji-picker-element@1.21.0/index.js';

        window.EmojiPicker = Picker;
    </script>

    <!-- Pusher -->
    <script src="{{ asset('js/pusher.min.js') }}"></script>

    <!-- Laravel Echo -->
    <script src="{{ asset('js/echo.iife.js') }}"></script>

    <!-- Chat Application -->
    <script src="{{ asset('js/chat-app.js') }}"></script>

    <!-- Initialize Chat App -->
    <script>
        $(document).ready(function() {
            const currentUser = @json(auth()->user());
            const pusherKey = '{{ config('broadcasting.connections.pusher.key') }}';
            const pusherCluster = '{{ config('broadcasting.connections.pusher.options.cluster') }}';

            initChatApp(currentUser, pusherKey, pusherCluster);
        });
    </script>
@endpush
