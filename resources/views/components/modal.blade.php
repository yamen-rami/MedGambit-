@props(["id" => 1 , "text" => "Open Modal" ])

<span {{ $attributes->merge(['class' => "text-body"]) }} data-bs-toggle="modal" data-bs-target="#staticBackdrop{{ $id }}">
  {{ $text }}
</span>

<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">{{ $title ?? "Modal " }}</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{ $body ?? "" }}
      </div>
      <div class="modal-footer">
        {{ $footer ?? "" }}
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        
      </div>
    </div>
  </div>
</div>