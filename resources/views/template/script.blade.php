<script type="text/javascript">
    window.addEventListener("load", function() {
        setTimeout(function() {
            $("#preloader").fadeOut();
        }, 1000);
    });
</script>
<script>
    function setMenuActive() {
        var currentPagePath = window.location.pathname; // Get the current page path
        console.log('Current page path:', currentPagePath);
        // var currentPageUrl = window.location.href;
        if(currentPagePath !== '/') {
            var urlParts = currentPagePath.split("/");
            var pathParts = urlParts.slice(3);
            console.log(pathParts)
        } else {

        }

        // Loop through each menu item
        $('.sidebar-menu a.nav-link').each(function() {
            var menuItemUrl = $(this).attr('href'); // Get the menu item URL
            console.log('Menu item URL:', menuItemUrl);

            // Check if the current page path matches the menu item path
            if (currentPagePath === menuItemUrl) {
                // Add 'active' class to the parent 'li' element
                $(this).closest('li').addClass('active');

                // If the menu item has a dropdown, open it
                if ($(this).hasClass('has-dropdown')) {
                    $(this).siblings('.dropdown-menu').addClass('show');
                }

                // Exit the loop
                return false;
            }
        });
    }
</script>
<script src="{{ asset('assets/modules/jquery.min.js') }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="{{ asset('assets/modules/popper.js') }}"></script>
<script src="{{ asset('assets/modules/tooltip.js') }}"></script>
<script src="{{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
<script src="{{ asset('assets/modules/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/stisla.js') }}"></script>

<!-- JS Libraies -->
<script src="{{ asset('assets/modules/simple-weather/jquery.simpleWeather.min.js') }}"></script>
<script src="{{ asset('assets/modules/chart.min.js') }}"></script>
<script src="{{ asset('assets/modules/jqvmap/dist/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('assets/modules/jqvmap/dist/maps/jquery.vmap.world.js') }}"></script>
<script src="{{ asset('assets/modules/summernote/summernote-bs4.js') }}"></script>
<script src="{{ asset('assets/modules/chocolat/dist/js/jquery.chocolat.min.js') }}"></script>

<!-- Page Specific JS File -->
<script src="{{ asset('assets/js/page/index-0.js') }}"></script>

<!-- Template JS File -->
<script src="{{ asset('assets/js/scripts.js') }}"></script>
<script src="{{ asset('assets/js/custom.js') }}"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap 5 integration -->
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/3.3.4/js/dataTables.fixedColumns.min.js"></script>
@yield('scripts')