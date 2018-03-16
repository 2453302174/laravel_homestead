<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('bootstrap4/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        .invalid-feedback {
        	display: block;
        }
        
        table.tbs1 th{
        	font-size: 14px;
        	color: #254;
        }
        table.tbs1 td{
        	font-size: 12px;
        	padding: 1px;
        	color: #254;
        	vertical-align:middle;
        }
        /*
        table.tbs1 td.rowspan{        
        	border-width: 3px;
        	border-right-width: 1px;
        }
        */
        .card.small .card-header {
        	padding: 2px 10px;
        }
        .input-group {
        	margin-top: 10px;
        }
        .input-group, .input-group-prepend, .input-group-text {
        	line-height: 1;
        }
        .input-group input, .input-group select {
        	padding: 1px 6px;
        	line-height: 1;
        	height: 30px !important;
        }
        table.tbs1 td input[type="text"], table.tbs1 td select {
        	padding: 1px;
        	line-height: 1.2;
        }
                 
        table.tbs1 td.datetime{        
        	font-size: 11px;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', '测试的Homestead') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li><a class="nav-link" href="{{ route('login') }}">登录</a></li>
                            <li><a class="nav-link" href="{{ route('register') }}">注册</a></li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        退出登录
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/jquery.slim.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('bootstrap4/js/bootstrap.min.js') }}"></script>
    @section('pageend')
        
    @show
</body>
</html>
