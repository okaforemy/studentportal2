<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">

 
  <link rel="stylesheet" href="{{mix('css/app.css')}}">
  <script src="{{mix('js/app.js')}}" defer></script>

<style>
  #nprogress .bar{
    z-index: 9999 !important;
  }
  disabled{
    cursor: crosshair;
  }
  .errors{
    color: red;
  }
  label{
    font-weight: 400 !important;
    color: #6a6767 !important;
  }
  .form-control, .custom-select {
    height: calc(2.5rem + 2px) !important;
    font-size: 1.1rem !important;
  }
  body {
    font-family: 'Josefin Sans', sans-serif;
}

  
</style>

</head>
<body class="hold-transition sidebar-mini">
@inertia
{{-- 
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script> --}}

</body>
</html>
