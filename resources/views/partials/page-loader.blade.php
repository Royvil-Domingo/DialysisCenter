<div id="pageLoader" class="page-loader" role="status" aria-live="polite" aria-busy="true" aria-label="Loading application content">
    <div class="page-loader-shell" aria-hidden="true">
        <aside class="page-loader-sidebar">
            <div class="page-loader-brand">
                <div class="skeleton-block skeleton-circle"></div>
                <div class="skeleton-block skeleton-line skeleton-line-short"></div>
            </div>
            <div class="page-loader-nav">
                @for ($i = 0; $i < 7; $i++)
                    <div class="skeleton-block skeleton-line skeleton-line-nav"></div>
                @endfor
            </div>
        </aside>

        <div class="page-loader-main">
            <header class="page-loader-topbar">
                <div class="skeleton-block skeleton-line skeleton-line-xs"></div>
                <div class="skeleton-block skeleton-circle skeleton-avatar"></div>
            </header>

            <div class="page-loader-heading">
                <div class="skeleton-block skeleton-line skeleton-line-lg"></div>
                <div class="skeleton-block skeleton-line skeleton-line-md"></div>
            </div>

            <div class="page-loader-grid">
                @for ($i = 0; $i < 4; $i++)
                    <div class="skeleton-card">
                        <div class="skeleton-block skeleton-circle skeleton-icon"></div>
                        <div class="skeleton-block skeleton-line skeleton-line-md"></div>
                        <div class="skeleton-block skeleton-line skeleton-line-sm"></div>
                    </div>
                @endfor
            </div>

            <div class="page-loader-panels">
                <div class="skeleton-panel skeleton-panel-lg">
                    <div class="skeleton-block skeleton-line skeleton-line-md"></div>
                    <div class="skeleton-block skeleton-line"></div>
                    <div class="skeleton-block skeleton-line"></div>
                    <div class="skeleton-block skeleton-line"></div>
                </div>
                <div class="skeleton-panel">
                    <div class="skeleton-block skeleton-line skeleton-line-md"></div>
                    <div class="skeleton-block skeleton-line"></div>
                    <div class="skeleton-block skeleton-line"></div>
                    <div class="skeleton-block skeleton-line"></div>
                </div>
            </div>
        </div>
    </div>
</div>
