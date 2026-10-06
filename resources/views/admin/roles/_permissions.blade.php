@php
    $selected = collect($selected ?? []);
    $locked = $locked ?? false;
@endphp
<div class="row perm-grid">
    @foreach($permissions as $module => $items)
        <div class="col-md-6 col-xl-4 mb-3">
            <div class="perm-card">
                <div class="perm-card-head">
                    <h6>{{ $modules[$module] ?? $module }}</h6>
                    @unless($locked)
                        <label class="perm-all">
                            <input type="checkbox" class="js-perm-all">
                            <span>الكل</span>
                        </label>
                    @endunless
                </div>
                @foreach($items as $p)
                    <label class="perm-item">
                        <input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked($selected->contains($p->id)) @disabled($locked)>
                        <span>{{ $p->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@once
<script>
document.querySelectorAll('.perm-card').forEach(function (card) {
    var all = card.querySelector('.js-perm-all');
    var boxes = card.querySelectorAll('input[name="permissions[]"]');
    if (!all || !boxes.length) return;
    function refresh() {
        all.checked = Array.prototype.every.call(boxes, function (box) { return box.checked; });
    }
    all.addEventListener('change', function () {
        boxes.forEach(function (box) { box.checked = all.checked; });
    });
    boxes.forEach(function (box) { box.addEventListener('change', refresh); });
    refresh();
});
</script>
@endonce
