@php
    $scrollTo = $scrollTo ?? '#questionList';
    $scrollIntoViewJsSnippet = $scrollTo !== false
        ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()"
        : '';
@endphp

@if ($paginator->hasPages())
    <nav class="gambit-pagination" role="navigation" aria-label="Question pagination">
        <ul class="gambit-pagination-list">
            <li class="gambit-pagination-item">
                @if ($paginator->onFirstPage())
                    <span class="gambit-pagination-control is-disabled" aria-disabled="true">
                        <span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
                        <span>Previous</span>
                    </span>
                @else
                    <button class="gambit-pagination-control" type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="Previous page">
                        <span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
                        <span>Previous</span>
                    </button>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="gambit-pagination-item is-ellipsis" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="gambit-pagination-item">
                            @if ($page == $paginator->currentPage())
                                <span class="gambit-pagination-page is-current" aria-current="page">{{ $page }}</span>
                            @else
                                <button class="gambit-pagination-page" type="button"
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}" aria-label="Go to page {{ $page }}">{{ $page }}</button>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li class="gambit-pagination-item">
                @if ($paginator->hasMorePages())
                    <button class="gambit-pagination-control" type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="Next page">
                        <span>Next</span>
                        <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                    </button>
                @else
                    <span class="gambit-pagination-control is-disabled" aria-disabled="true">
                        <span>Next</span>
                        <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
