@extends('layouts.app')

@section('content')
    <div class="flex flex-col items-center pt-20 sm:pt-32">
        
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg border border-gray-200 dark:border-gray-700">

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Name')" class="dark:text-gray-200" />
                    <x-text-input id="name" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="email" :value="__('Email')" class="dark:text-gray-200" />
                    <x-text-input id="email" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" class="dark:text-gray-200" />
                    <x-text-input id="password" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="dark:text-gray-200" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4 gap-6">
                    
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <x-primary-button class="!bg-indigo-600 hover:!bg-indigo-500 !text-white border-transparent focus:ring-indigo-500">
                        {{ __('Register') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
@endsection