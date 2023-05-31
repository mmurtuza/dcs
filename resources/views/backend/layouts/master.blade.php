<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
		<meta charset="utf-8" />
		<title>@yield('title')</title>
		<meta name="description" content="Header dark theme example" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Page Vendors Styles(used by this page)-->
		<link href="{{ asset('ui/backend/plugins/custom/fullcalendar/fullcalendar.bundlec7e5.css?v=7.1.1') }}" rel="stylesheet" type="text/css" />
		<!--end::Page Vendors Styles-->
		<!--begin::Global Theme Styles(used by all pages)-->
		<link href="{{ asset('ui/backend/plugins/global/plugins.bundlec7e5.css?v=7.1.1') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('ui/backend/plugins/custom/prismjs/prismjs.bundlec7e5.css?v=7.1.1') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('ui/backend/css/style.bundlec7e5.css?v=7.1.1') }}" rel="stylesheet" type="text/css" />
		<!--end::Global Theme Styles-->
		<!--begin::Layout Themes(used by all pages)-->
		<link href="{{ asset('ui/backend/css/themes/layout/header/base/darkc7e5.css?v=7.1.1') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('ui/backend/css/themes/layout/header/menu/darkc7e5.css?v=7.1.1') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('ui/backend/css/themes/layout/brand/darkc7e5.css?v=7.1.1') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('ui/backend/css/themes/layout/aside/darkc7e5.css?v=7.1.1') }}" rel="stylesheet" type="text/css" />
    {{-- datatables --}}
    <link href="{{ asset('ui/backend/plugins/custom/datatables/datatables.bundlec7e5.css?v=7.1.1') }}" rel="stylesheet" type="text/css" />
		<!--end::Layout Themes-->
		<link rel="shortcut icon" href="{{ asset('ui/backend/media/logos/brac.png') }}" />
<link href="{{ asset('ui/backend/js/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />

		<!-- Hotjar Tracking Code for keenthemes.com -->
		@yield('style')
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="header-fixed header-mobile-fixed subheader-enabled subheader-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">
		<!--begin::Main-->
		<!--begin::Header Mobile-->
		<div id="kt_header_mobile" class="header-mobile align-items-center header-mobile-fixed">
			<!--begin::Logo-->
			<a href="../../index.html">
				<h3 style="color: #FB3199;margin-top: 8px;">Brac Microfinence</h3>
			</a>
			<!--end::Logo-->
			<!--begin::Toolbar-->
			<div class="d-flex align-items-center">
				<!--begin::Aside Mobile Toggle-->
				<button class="btn p-0 burger-icon burger-icon-left" id="kt_aside_mobile_toggle">
					<span></span>
				</button>
				<!--end::Aside Mobile Toggle-->
				<!--begin::Header Menu Mobile Toggle-->
				{{-- <button class="btn p-0 burger-icon ml-4" id="kt_header_mobile_toggle">
					<span></span>
				</button> --}}
				<!--end::Header Menu Mobile Toggle-->
				<!--begin::Topbar Mobile Toggle-->
				<button class="btn btn-hover-text-primary p-0 ml-2" id="kt_header_mobile_topbar_toggle">
					<span class="svg-icon svg-icon-xl">
						<!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/General/User.svg-->
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
							<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<polygon points="0 0 24 0 24 24 0 24" />
								<path d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" />
								<path d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z" fill="#000000" fill-rule="nonzero" />
							</g>
						</svg>
						<!--end::Svg Icon-->
					</span>
				</button>
				<!--end::Topbar Mobile Toggle-->
			</div>
			<!--end::Toolbar-->
		</div>
		<!--end::Header Mobile-->
		<div class="d-flex flex-column flex-root">
			<!--begin::Page-->
			<div class="d-flex flex-row flex-column-fluid page">
        <!--begin::Aside-->

        @include('backend.layouts.partials.sidebar')

				<!--end::Aside-->
				<!--begin::Wrapper-->
				<div class=" app d-flex flex-column flex-row-fluid wrapper" id="kt_wrapper">
          <!--begin::Header-->

          <div class="app">



          @include('backend.layouts.partials.header')


					<!--end::Header-->
					<!--begin::Content-->

          @yield('content')

					<!--end::Content-->
                    <!--begin::Footer-->

          @include('backend.layouts.partials.footer')

					<!--end::Footer-->
				</div>
				<!--end::Wrapper-->
			</div>
            </div>
			<!--end::Page-->
		</div>
		<!--end::Main-->
		<!--begin::Scrolltop-->
		<div id="kt_scrolltop" class="scrolltop">
			<span class="svg-icon">
				<!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/Navigation/Up-2.svg-->
				<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
					<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<polygon points="0 0 24 0 24 24 0 24" />
						<rect fill="#000000" opacity="0.3" x="11" y="10" width="2" height="10" rx="1" />
						<path d="M6.70710678,12.7071068 C6.31658249,13.0976311 5.68341751,13.0976311 5.29289322,12.7071068 C4.90236893,12.3165825 4.90236893,11.6834175 5.29289322,11.2928932 L11.2928932,5.29289322 C11.6714722,4.91431428 12.2810586,4.90106866 12.6757246,5.26284586 L18.6757246,10.7628459 C19.0828436,11.1360383 19.1103465,11.7686056 18.7371541,12.1757246 C18.3639617,12.5828436 17.7313944,12.6103465 17.3242754,12.2371541 L12.0300757,7.38413782 L6.70710678,12.7071068 Z" fill="#000000" fill-rule="nonzero" />
					</g>
				</svg>
				<!--end::Svg Icon-->
			</span>
		</div>
		<!--end::Scrolltop-->
		<script>var HOST_URL = "https://preview.keenthemes.com/metronic/theme/html/tools/preview";</script>
		<!--begin::Global Config(global config for global JS scripts)-->
		<script>var KTAppSettings = { "breakpoints": { "sm": 576, "md": 768, "lg": 992, "xl": 1200, "xxl": 1400 }, "colors": { "theme": { "base": { "white": "#ffffff", "primary": "#3699FF", "secondary": "#E5EAEE", "success": "#1BC5BD", "info": "#8950FC", "warning": "#FFA800", "danger": "#F64E60", "light": "#E4E6EF", "dark": "#181C32" }, "light": { "white": "#ffffff", "primary": "#E1F0FF", "secondary": "#EBEDF3", "success": "#C9F7F5", "info": "#EEE5FF", "warning": "#FFF4DE", "danger": "#FFE2E5", "light": "#F3F6F9", "dark": "#D6D6E0" }, "inverse": { "white": "#ffffff", "primary": "#ffffff", "secondary": "#3F4254", "success": "#ffffff", "info": "#ffffff", "warning": "#ffffff", "danger": "#ffffff", "light": "#464E5F", "dark": "#ffffff" } }, "gray": { "gray-100": "#F3F6F9", "gray-200": "#EBEDF3", "gray-300": "#E4E6EF", "gray-400": "#D1D3E0", "gray-500": "#B5B5C3", "gray-600": "#7E8299", "gray-700": "#5E6278", "gray-800": "#3F4254", "gray-900": "#181C32" } }, "font-family": "Poppins" };</script>
		<!--end::Global Config-->
		<!--begin::Global Theme Bundle(used by all pages)-->
		<script src="{{ asset('ui/backend/plugins/global/plugins.bundlec7e5.js?v=7.1.1') }}"></script>
		<script src="{{ asset('ui/backend/plugins/custom/prismjs/prismjs.bundlec7e5.js?v=7.1.1') }}"></script>
		<script src="{{ asset('ui/backend/js/scripts.bundlec7e5.js?v=7.1.1') }}"></script>
    <!--end::Global Theme Bundle-->
    {{-- datatable --}}
    <script src="{{ asset('ui/backend/plugins/custom/datatables/datatables.bundlec7e5.js?v=7.1.1') }}"></script>
    <script src="{{ asset('ui/backend/js/pages/crud/datatables/basic/paginationsc7e5.js?v=7.1.1') }}"></script>

    @yield('script')
	<script>
		$('#kt_aside_toggle').click(function() {
			$('#main_logo_brac').toggle();
		});
	  </script>
		<!--begin::Page Vendors(used by this page)-->
		<script src="{{ asset('ui/backend/plugins/custom/fullcalendar/fullcalendar.bundlec7e5.js?v=7.1.1') }}"></script>
		{{-- <script src="http://maps.google.com/maps/api/js?key=AIzaSyBTGnKT7dt597vo9QgeQ7BFhvSRP4eiMSM?v=7.1.1"></script> --}}
		<script src="{{ asset('ui/backend/plugins/custom/gmaps/gmapsc7e5.js?v=7.1.1') }}"></script>
		<!--end::Page Vendors-->
		<!--begin::Page Scripts(used by this page)-->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script> -->

<script src="{{ asset('ui/backend/js/select2/js/select2.min.js') }}"></script>
		<script src="{{ asset('ui/backend/js/pages/widgetsc7e5.js?v=7.1.1') }}"></script>
		@vite('resources/js/app.js')
		<!--end::Page Scripts-->
	</body>
	<!--end::Body-->

<!-- Mirrored from preview.keenthemes.com/metronic/demo1/layout/themes/header-dark.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 17 Sep 2020 08:24:41 GMT -->
</html>
