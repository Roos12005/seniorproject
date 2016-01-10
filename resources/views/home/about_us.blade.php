@extends('layout.default')

@section('title', 'AIS - CU : About Us')

@section('stylesheet')

@section('content')

 <div class="row">
    <div class="col-md-12">

        <section class="panel">
            <header class="panel-heading">
                Senior Project
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                 </span>
            </header>
            <div class="panel-body">
                ...
            </div>
        </section>
        <section class="panel">
            <div class="panel-body profile-information">
                <div class="col-md-3">
                    <div class="profile-pic text-center">
                        <img src="images/Kim.jpg" alt=""/>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="profile-desk">
                        <h1>Nattapon Werayawarangura</h1>
                        <span class="text-muted">Project Developer</span><br>
                        <p>
                           Chulalongkorn University
                        </p>
                     </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-body profile-information">
                <div class="col-md-3">
                    <div class="profile-pic text-center">
                        <img src="images/Phoom.jpg" alt=""/>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="profile-desk">
                        <h1>Thanaphoom Pungchaichan</h1>
                        <span class="text-muted">Project Developer</span><br>
                        <p>
                           Chulalongkorn University
                        </p>
                     </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-body profile-information">
                <div class="col-md-3">
                    <div class="profile-pic text-center">
                        <img src="images/Aj.Peerapon.jpg" alt=""/>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="profile-desk">
                        <h1>Dr. Peerapon Vateekul</h1>
                        <span class="text-muted">Project Administrator</span><br>
                        <p>
                           Chulalongkorn University
                        </p>
                     </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-body profile-information">
                <div class="col-md-3">
                    <div class="profile-pic text-center">
                        <img src="images/Aj.Natawut.jpg" alt=""/>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="profile-desk">
                        <h1>Asst.Prof. Dr.Natawut Nupairoj</h1>
                        <span class="text-muted">Project Advicer</span><br>
                        <p>
                           Chulalongkorn University 
                        </p>
                     </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-body profile-information">
                <div class="col-md-3">
                    <div class="profile-pic text-center">
                        <img src="images/Aj.Veera.jpg" alt=""/>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="profile-desk">
                        <h1>Asst.Prof. Dr.Veera Muangsin</h1>
                        <span class="text-muted">Project Advicer</span><br>
                        <p>
                           Chulalongkorn University
                        </p>
                     </div>
                </div>
            </div>
        </section>

    </div>
</div>


<!-- Other Statistic Section End -->
@section('bottom-script')
{!! Html::script('js/jquery.js'); !!}


@endsection
@stop