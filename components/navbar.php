<div class="flex-1">

<nav class="bg-white shadow-sm px-6 py-4 flex justify-between items-center">

<div class="flex items-center gap-3">

<button
onclick="toggleSidebar()"
class="lg:hidden bg-slate-100 p-2 rounded-lg">

☰

</button>

<h1 class="font-semibold text-gray-700">

Dashboard Pengasuh

</h1>

</div>

<div class="flex items-center gap-4">

<div class="text-right">

<p class="font-semibold">

<?= $_SESSION['nama']; ?>

</p>

<p class="text-sm text-gray-500">

<?= ucfirst($_SESSION['role']); ?>

</p>

</div>

<div
class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white font-bold">

<?= strtoupper(substr($_SESSION['nama'],0,1)); ?>

</div>

</div>

</nav>