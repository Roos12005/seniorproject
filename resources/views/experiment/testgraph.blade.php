@extends('layout.default')

@section('title', 'SigmaJS Experiment')


@section('content')
<style type="text/css">
    body {
      margin: 0;
  }
  #container {
      position: relative;
      width: 70%;
      height: 70%;
  }
</style>
<div id="container"></div>


@section('bottom-script')
    {!! Html::script('js/jquery-1.8.3.min.js'); !!}
    {!! Html::script('js/sigmajs/sigma.min.js'); !!}
    {!! Html::script('js/experiment.js'); !!}
@endsection
@stop