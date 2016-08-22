@extends('layout.default')

@section('title', 'AIS - CU : Main Page')

@section('stylesheet')

    {!! Html::style('js/bootstrap-datepicker/css/datepicker.css') !!}
    {!! Html::style('js/select2/select2.css') !!}
    {!! Html::style('js/iCheck/skins/flat/_all.css') !!}
    

@section('content')
<div id="container"></div>
<!-- Other Statistic Section End -->
@section('bottom-script')
{!! Html::script('js/jquery.js'); !!}
{!! Html::script('js/sigmajs/sigma.min.js'); !!}
{!! Html::script('js/sigmajs/plugins/sigma.layout.forceAtlas2.min.js'); !!}
<script>
  

  $.ajax({
        type: "GET",
        url: "http://localhost:8000/test/getxy",
        data : {},
        success: function(e){
          console.log('!!!');
          var s = new sigma({
                  renderers: [{
                      container: document.getElementById('container'),
                  }]
                });
            e.forEach(function(n) {
              
                s.graph.addNode({
                  id: n.x,
                  label: n.x,
                  x: n.x,
                  y: n.y,
                  size: 0.2,
              })
            })
            s.startForceAtlas2({});
        },
        error: function(rs, e){
            console.log(rs.responseText);
            alert('Problem occurs during fetch data.');
        }
    })
</script>

<!-- Date Range (Date Picker) -->
{!! Html::script('bs3/js/bootstrap.min.js'); !!}
{!! Html::script('js/bootstrap-datepicker/js/bootstrap-datepicker.js'); !!}
{!! Html::script('js/bootstrap-switch.js'); !!}
{!! Html::script('js/main.js'); !!}

<!-- Date Range (Dropdown) -->
{!! Html::script('js/select2/select2.js'); !!}
{!! Html::script('js/select-init.js'); !!}

<!--Check Box -->
{!! Html::script('js/iCheck/jquery.icheck.js'); !!}
{!! Html::script('js/icheck-init.js'); !!}

<!--Morris Chart-->
{!! Html::script('js/morris-chart/morris.js'); !!}
{!! Html::script('js/morris-chart/raphael-min.js'); !!}

@endsection
@stop