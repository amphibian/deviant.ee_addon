$(document).ready(function()
{
	$("select[name^=global_new_deviant]").change(function(){
		var setValue = $(this).val();
		if(setValue != "none")
		{
			$("select[name^=new_channel_id] option[value=" + setValue + "]").attr("selected", "selected");
			$("select[name^=new_channel_id]").attr("disabled", "disabled"); 
		}
		else
		{
			$("select[name^=new_channel_id]").removeAttr("disabled");
		}						
	});
	$("select[name^=global_updated_deviant]").change(function(){
		var setValue = $(this).val();
		if(setValue != "none")
		{
			$("select[name^=updated_channel_id] option[value=" + setValue + "]").attr("selected", "selected");
			$("select[name^=updated_channel_id]").attr("disabled", "disabled"); 
		}
		else
		{
			$("select[name^=updated_channel_id]").removeAttr("disabled");
		}
	});

});