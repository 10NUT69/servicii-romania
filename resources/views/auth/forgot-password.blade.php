@extends('layouts.app')

@section('content')
    <div class="flex flex-col items-center pt-20 sm:pt-32">

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg border border-gray-200 dark:border-gray-700">
            
            <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('Email')" class="dark:text-gray-200" />
                    
                    <x-text-input id="email" 
                                  class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500" 
                                  type="email" 
                                  name="email" 
                                  :value="old('email')" 
                                  required autofocus />
                                  
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-primary-button class="!bg-indigo-600 hover:!bg-indigo-500 !text-white border-transparent">
                        {{ __('Email Password Reset Link') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
@endsection