<!--Tracking number input box.-->
<input type="text" id="YQNum" maxlength="50"/>
<!--The button is used to call script method.-->
<input type="button" value="TRACK" onclick="doTrack()"/>
<!--Container to display the tracking result.-->
<div id="Aveeto_YQContainer"></div>

<!--Script code can be put in the bottom of the page, wait until the page is loaded then execute.-->
<script type="text/javascript" src="//www.17track.net/externalcall.js"></script>
<script type="text/javascript">
function doTrack() {
    var num = document.getElementById("YQNum").value;
    if(num===""){
        alert("Enter your number."); 
        return;
    }
    YQV5.trackSingle({
        //Required, Specify the container ID of the carrier content.
        YQ_ContainerId:"Aveeto_YQContainer",
        //Optional, specify tracking result height, max height 800px, default is 560px.
        YQ_Height:560,
        //Optional, select carrier, default to auto identify.
        YQ_Fc:"0",
        //Optional, specify UI language, default language is automatically detected based on the browser settings.
        YQ_Lang:"en",
        //Required, specify the number needed to be tracked.
        YQ_Num:num
    });
}
</script>