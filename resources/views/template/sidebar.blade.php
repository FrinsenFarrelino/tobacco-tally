<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="/">Tobacco Tally</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="/">TY</a>
        </div>
        <ul class="sidebar-menu">
            @if (Session::get('list_menu'))
            <?php
            echo generateMenu(Session::get('list_menu'))
            ?>
            @endif
        </ul>
    </aside>
</div>