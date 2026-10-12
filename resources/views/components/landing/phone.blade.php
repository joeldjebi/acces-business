<div {{ $attributes->merge(['class' => 'phone']) }}>
    <span class="phone-btn phone-btn-a"></span><span class="phone-btn phone-btn-b"></span><span class="phone-btn phone-btn-c"></span>
    <div class="phone-screen">
        <span class="island"></span>
        {{ $slot }}
    </div>
</div>
