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
                <div class="text-center">
                    <img src="images/ais-cu.jpg" width="600" height"450" />
                </div>  
                <p>
                    <br/>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed placerat leo vel facilisis convallis. 
                    Curabitur fringilla posuere laoreet. Aenean mauris justo, sodales ut est vel, scelerisque condimentum nunc. 
                    Donec sed sagittis libero, id ullamcorper lectus. Sed lobortis urna eu leo semper efficitur. Etiam et nisi est. 
                    Nunc nisl tellus, viverra aliquam lectus at, sollicitudin volutpat orci.Integer eu lacus sapien. 
                    Praesent ultrices consequat elit et dignissim. Sed at tempor augue. Nullam egestas varius dolor, id 
                    sollicitudin ipsum vestibulum id. Donec porta vel tortor non dignissim. Mauris elit neque, tincidunt a 
                    justo id, pretium ultricies lacus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Class 
                    aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Quisque libero nunc, 
                    fringilla sed convallis non, egestas non arcu. Aliquam tincidunt odio quis dui elementum egestas.
                </p>
            </div>
        </section>
        <h4> Project Staffs : </h4>
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

        

    </div>
</div>


<!-- Other Statistic Section End -->
@section('bottom-script')
{!! Html::script('js/jquery.js'); !!}


@endsection
@stop
 
