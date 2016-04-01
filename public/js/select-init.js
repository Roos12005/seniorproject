$(document).ready(function() {
    $("#e1").select2();
    $("#e9").select2();
    $("#e2").select2({
        placeholder: "Select Community ID",
        allowClear: true
    });
    $("#e3").select2({
        minimumInputLength: 2
    });
    $("#memberProfile").select2({placeholder: "All Level"});
    $("#daytimeNighttimeProfile").select2({placeholder: "All Level"});
    $("#weekdayWeekendProfile").select2({placeholder: "All Level"});
    $("#aisRatioProfile").select2({placeholder: "All Level"});
    $("#callOtherCarrierProfile").select2({placeholder: "All Level"});
    $("#averageNoOfCallProfile").select2({placeholder: "All Level"});
    $("#averageArpuProfile").select2({placeholder: "All Level"});
    $("#averageDurationProfile").select2({placeholder: "All Level"});

    $("#memberProfileExport").select2({placeholder: "All Level"});
    $("#daytimeNighttimeProfileExport").select2({placeholder: "All Level"});
    $("#weekdayWeekendProfileExport").select2({placeholder: "All Level"});
    $("#aisRatioProfileExport").select2({placeholder: "All Level"});
    $("#callOtherCarrierProfileExport").select2({placeholder: "All Level"});
    $("#averageNoOfCallProfileExport").select2({placeholder: "All Level"});
    $("#averageArpuProfileExport").select2({placeholder: "All Level"});
    $("#averageDurationProfileExport").select2({placeholder: "All Level"});

    $('#spinner1').spinner({value:0, min: 0, max: 9999});
});

