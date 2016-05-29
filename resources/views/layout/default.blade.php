<!DOCTYPE html>
<html lang="en">
    <head>
    	<meta charset="UTF-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<meta name="description" content="">
    	<meta name="author" content="ThemeBucket">

	    <!--Core CSS -->
	    {!! Html::style('bs3/css/bootstrap.min.css') !!}
	    {!! Html::style('js/jquery/jquery-ui/jquery-ui-1.10.1.custom.min.css') !!}
	    {!! Html::style('css/bootstrap-reset.css') !!}
	    {!! Html::style('font-awesome/css/font-awesome.css') !!}
	    {!! Html::style('js/bucket-admin/jvector-map/jquery-jvectormap-1.2.2.css') !!}
	    {!! Html::style('css/clndr.css') !!}
	    <!--clock css-->
	    {!! Html::style('js/bucket-admin/css3clock/css/style.css') !!}
	    <!--Morris Chart CSS -->
	    {!! Html::style('js/bucket-admin/morris-chart/morris.css') !!}
	    
	    @yield('stylesheet')

	    <!-- Custom styles for this template -->
	    {!! Html::style('css/style.css') !!}
	    {!! Html::style('css/style-responsive.css') !!}

    	
        <title>@yield('title')</title>
    </head>
    <body>
    	<div id="loading-overlay" hidden>
    		<div class="loader"></div>
    	</div>
	    <section id="m-container">
			<!--header start-->
			<header class="header fixed-top clearfix">
				<!--logo start-->
				<div class="brand">

				    <a href="/seniorproject/public" class="logo text-center">
				        <h4><strong>AIS - CU</strong> Project</h4>
				    </a>
				    <div class="sidebar-toggle-box">
				        <div class="fa fa-bars"></div>
				    </div>
				</div>
				<!--logo end-->		
				<div class="top-nav clearfix">				    
				    <ul class="nav pull-right top-menu">
				    	<img src="/seniorproject/public/images/ais.jpg" id="ais-logo">  
				    	<img src="/seniorproject/public/images/ChulaEngineering.png" id="chula-logo">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				    	
				    </ul>
				</div>	
			</header>
			<!--header end-->
			<!--sidebar start-->
			<aside>
			    <div id="sidebar" class="nav-collapse">
			        <!-- sidebar menu start-->
			        <div class="leftside-navigation">
			            <ul class="sidebar-menu" id="nav-accordion">
			            	<li>
			                    <a class="active" href="/seniorproject/public">
			                        <i class="fa fa-dashboard"></i>
			                        <span>Social Network Analysis</span>
			                    </a>
			                </li>
			                <li>
			                    <a class="active" href="http://localhost:8080">
			                        <i class="fa fa-cloud"></i>
			                        <span>Spark Mode</span>
			                    </a>
			                </li>
			                <li>
			                    <a class="active" href="/seniorproject/public/database">
			                        <i class="fa fa-hdd-o"></i>
			                        <span>Database Manager</span>
			                    </a>
			                </li>
			                <li>
			                    <a class="active" href="/seniorproject/public/aboutus">
			                        <i class="fa fa-user"></i>
			                        <span>About Us</span>
			                    </a>
			                </li>					                
			                
			            </ul>            
			        </div>
			        <!-- sidebar menu end-->
			    </div>
			</aside>
			<section id="main-content" class="">
				<section class="wrapper">
	        		@yield('content')
	    		</section>
			</section>
		</section>

        @yield('bottom-script')

        <!-- Side Bar -->
	{!! Html::script('js/jquery/jquery.nicescroll.js'); !!}
	{!! Html::script('js/scripts.js'); !!}

    </body>
</html>