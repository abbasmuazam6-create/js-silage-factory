<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-white/80 backdrop-blur-sm">
    <div class="flex flex-col items-center">
      <!-- Logo with Ring -->
      <div class="relative flex items-center justify-center">
        <!-- Dashed Spinning Ring -->
        <svg
          class="absolute h-48 w-48 animate-spin-slow"
          viewBox="0 0 100 100"
        >
          <circle
            cx="50"
            cy="50"
            r="42"
            fill="none"
            stroke="#10B981"
            stroke-width="4"
            stroke-dasharray="15 10"
            stroke-linecap="round"
            class="opacity-80"
          />
        </svg>

        <!-- Pulsing Glow Ring -->
        <svg
          class="absolute h-52 w-52 animate-pulse-ring"
          viewBox="0 0 100 100"
        >
          <circle
            cx="50"
            cy="50"
            r="44"
            fill="none"
            stroke="rgba(16, 185, 129, 0.15)"
            stroke-width="6"
          />
        </svg>

        <!-- Logo -->
        <img
          src="https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg"
          alt="JS Silage Logo"
          class="relative h-28 w-28 object-contain animate-pulse-logo"
        />
      </div>

      <!-- Loading Text -->
      <p class="mt-6 text-sm font-medium text-slate-600 animate-pulse-text">
        Loading Dashboard...
      </p>

      <!-- Bouncing Dots -->
      <div class="mt-3 flex items-center gap-2">
        <span
          class="h-3 w-3 rounded-full bg-emerald-500 animate-bounce-dot"
          style="animation-delay: 0ms"
        ></span>
        <span
          class="h-3 w-3 rounded-full bg-emerald-500 animate-bounce-dot"
          style="animation-delay: 150ms"
        ></span>
        <span
          class="h-3 w-3 rounded-full bg-emerald-500 animate-bounce-dot"
          style="animation-delay: 300ms"
        ></span>
      </div>
    </div>
  </div>
</template>

<script setup>
// No props needed - simple loader for Dashboard only
</script>

<style scoped>
/* === Spinning Dashed Ring === */
.animate-spin-slow {
  animation: spin-ring 2.5s linear infinite;
}

@keyframes spin-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* === Pulsing Glow Ring === */
.animate-pulse-ring {
  animation: pulse-ring 2s ease-in-out infinite;
}

@keyframes pulse-ring {
  0%, 100% {
    transform: scale(1);
    opacity: 0.6;
  }
  50% {
    transform: scale(1.08);
    opacity: 1;
  }
}

/* === Pulsing Logo === */
.animate-pulse-logo {
  animation: pulse-logo 2s ease-in-out infinite;
}

@keyframes pulse-logo {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.06);
  }
}

/* === Pulsing Text === */
.animate-pulse-text {
  animation: pulse-text 1.5s ease-in-out infinite;
}

@keyframes pulse-text {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.6;
  }
}

/* === Bouncing Dots === */
.animate-bounce-dot {
  animation: bounce-dot 0.8s ease-in-out infinite;
}

@keyframes bounce-dot {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}
</style>