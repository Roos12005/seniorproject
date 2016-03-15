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
                <p style ="margin-left:40px; margin-right:40px;">
                    <br><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;คณะวิศวกรรมศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย โดย ภาควิชาวิศวกรรมคอมพิวเตอร์ ได้ทำข้อตกลงความร่วมมือกับบริษัท แอดวานซ์ อินโฟร์ เซอร์วิส จำกัด (มหาชน) ในการดำเนินโครงการวิจัยร่วมเพื่อนำเทคโนโลยี Big Data มาใช้ในการวิเคราะห์ข้อมูลการใช้งานบริการโทรศัพท์เคลื่อนที่ของลูกค้าเอไอเอส เพื่อแบ่งกลุ่มผู้ใช้บริการอย่างละเอียด หรือ ไมโครเซ็กเมนต์ ภายใต้แนวคิดที่ว่าผู้ใช้บริการแต่ละคนมีลักษณะที่แตกต่างกันในหลายมิติ เช่น ลักษณะทางประชากร สังคม เศรษฐกิจ พฤติกรรม และความสนใจเป็นต้น ซึ่งผลลัพธ์ของงานวิจัยจะช่วยให้เอไอเอสมีความเข้าใจในกลุ่มลูกค้าของบริษัทฯมากยิ่งขึ้น สามารถพัฒนาบริการที่สามารถตอบสนองความต้องการเฉพาะกลุ่มและการตลาดที่เจาะจงกลุ่มเป้าหมายได้เป็นอย่างดี
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
 
