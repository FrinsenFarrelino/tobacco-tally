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

        // Loop through each menu item
        $('.sidebar-menu a.nav-link').each(function() {
            var menuItemUrl = $(this).attr('href'); // Get the menu item URL

            // Check if the current page path matches the menu item path
            if (currentPagePath === menuItemUrl) {
                // Add 'active' class to the parent 'li' elements
                $(this).closest('li').addClass('active');
                $(this).closest('.dropdown').addClass('active').parent('.dropdown').addClass('active');

                // If the menu item has a dropdown, open it
                if ($(this).hasClass('has-dropdown')) {
                    $(this).siblings('.dropdown-menu').addClass('show');
                }

                return false;
            }
        });
    }

    function formatRupiah(number = 0, prefix) {
        var numberString = Number(number).toFixed(2),
            split = numberString.split('.'),
            integerPart = split[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.'), // Add commas to the integer part
            decimalPart = split[1] || '00';

        var rupiah = decimalPart !== '00' ? integerPart + ',' + decimalPart : integerPart;

        return prefix === undefined ? rupiah : (rupiah ? prefix + ' ' + rupiah : '');
    }

    function parseNumberWithDots(numberString) {
        // Remove all dots from the string and convert it to a number
        return Number(numberString.replace(/\./g, ''));
    }

    function showLoadingOverlay() {
        // Create the loading overlay and spinner
        $('<div id="loading-overlay"><div id="loading-spinner"></div></div>').appendTo('body');
    }

    function hideLoadingOverlay() {
        // Remove the loading overlay
        $('#loading-overlay').remove();
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
<script src="{{ asset('vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>

<!-- Page Specific JS File -->
<!-- <script src="{{ asset('assets/js/page/index-0.js') }}"></script> -->

<!-- Template JS File -->
<script src="{{ asset('assets/js/scripts.js') }}"></script>
<script src="{{ asset('assets/js/custom.js') }}"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap 5 integration -->
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/3.3.4/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="{{ asset('vendor/jquery-ui-jqgrid/jquery.jqGrid.src.js') }}"></script>
<script type="text/javascript" src="{{ asset('vendor/jquery-ui-jqgrid/jQuery.jqGrid.setColWidth.js') }}"></script>
<script type="text/javascript" src="{{ asset('vendor/jquery-ui-jqgrid/jQuery.jqGrid.autoWidthColumns.js') }}"></script>
@yield('scripts')