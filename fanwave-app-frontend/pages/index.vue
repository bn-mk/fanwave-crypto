<template>
  <!-- Simple test with basic Tailwind classes -->
  <div class="bg-blue-500 p-8 text-white">
    <div class="text-center">
      <h1 class="text-4xl font-bold mb-4">
        Fanwave Digital Crypto
      </h1>
      <p class="text-xl mb-8">
        Welcome to the future of digital cryptocurrency
      </p>
      <div class="flex gap-4 justify-center">
        <NuxtLink 
          to="/crypto" 
          class="bg-red-500 px-8 py-3 text-white rounded"
        >
          Get Started
        </NuxtLink>
        <button class="bg-transparent border-2 border-white px-8 py-3 text-white rounded">
          Learn More
        </button>
      </div>
    </div>
  </div>
</template>
