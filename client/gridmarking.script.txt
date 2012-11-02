integer chirp = 0;

integer enable_delicious = 0;
integer enable_magnolia = 0;

string delicious_username = "";
string delicious_password = "";

string magnolia_password = "";

string tags = "";
integer listening_tags = 0;

key http_delicious_post = NULL_KEY;
key http_magnolia_post = NULL_KEY;

key http_donator = NULL_KEY;

key data_config = NULL_KEY;
integer data_config_line = 0;

default
{
    
    changed(integer mask) {
        if(llGetAttached() != 0) {
             if(mask & CHANGED_OWNER) { llResetScript(); }
            if(mask & CHANGED_INVENTORY) { llResetScript(); }
        }
    }
    on_rez(integer param) { llResetScript(); }
    
    state_entry()
    {
        data_config = llGetNotecardLine("Configuration", data_config_line);
    }
    
    dataserver(key query_id, string data) {
        
        if(query_id != data_config) return;

        if(data == EOF) {
            
            data_config = NULL_KEY;

            if(enable_delicious && (delicious_username == "" || delicious_password == "")) {
                llDialog(llGetOwner(), "If you wish to enable del.icio.us support you must provide your username and password. Please check your Configuration card.", [], 1000);
            } else if(enable_magnolia && magnolia_password == "") {
                llDialog(llGetOwner(), "If you wish to enable Ma.gnolia support you must provide your API key, found on the 'Advanced' tab in your Ma.gnolia website profile. Please check your Configuration card.", [], 1000);
            } else {
                state active;
            }

            return;

        } else if(llStringLength(data) > 7 && llGetSubString(data, 0, 0) != "#") {

            list line = llParseString2List(data, ["="], []);
            string setting = llToLower(llStringTrim(llList2String(line, 0), STRING_TRIM));
            string value = llStringTrim(llList2String(line, 1), STRING_TRIM);

            integer boolvalue = 0;
            if(value == "on" || value == "yes" || value == "true" || value == "1") boolvalue = 1;

            if(llGetSubString(setting, 0, 8) == "delicious") {
                if(setting == "delicious") {
                    enable_delicious = boolvalue;
                } else if(setting == "delicious username") {
                    delicious_username = llEscapeURL(value);
                } else if(setting == "delicious password") {
                    delicious_password = llEscapeURL(value);
                }
            } if(setting == "magnolia") {
                enable_magnolia = boolvalue;
            } else if(setting == "magnolia api key") {
                magnolia_password = llEscapeURL(value);
            } else if(setting == "chirp") {
                chirp = boolvalue;
            } else if(setting == "configured") {
                if(!boolvalue) { return; }
            }

        }

        data_config = llGetNotecardLine("Configuration", ++data_config_line);
        return;

    }

}

state active {
    
    changed(integer mask) {
        if(llGetAttached() != 0) {
             if(mask & CHANGED_OWNER) { llResetScript(); }
            if(mask & CHANGED_INVENTORY) { llResetScript(); }
        }
    }
    on_rez(integer param) { llResetScript(); }
    
    state_entry()
    {
        if(enable_delicious || enable_magnolia) {
            llListen(0, "", llGetOwner(), "/bookmark");
            llOwnerSay("To create a social bookmark of a location, type '/bookmark' in chat.");
        }
    }
    
    listen(integer incoming_channel, string name, key id, string message) {
        
        if(id != llGetOwner()) return;
        
        if(incoming_channel == 0 && listening_tags == 1) {
            
            tags = llStringTrim(message, STRING_TRIM);
            llOwnerSay("Your tags for this bookmark will be: " + tags + "\nTo continue with these tags, press Finished. To change them, simply speak your new tags in chat.");
            
        } else if(incoming_channel == 8348729472 && message == "Finished") {
            
            listening_tags = 0;
            llListenRemove(8348729472);
            
            vector position = llGetPos();

            if(enable_delicious) {
                llOwnerSay("Contacting del.icio.us ...");
                http_delicious_post = llHTTPRequest("https://"+delicious_username+":"+delicious_password+"@api.del.icio.us/v1/posts/add", [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"], "description=Second+Life:+"+llEscapeURL((string)llGetParcelDetails(position,[PARCEL_DETAILS_NAME]))+llEscapeURL(" (" + llGetRegionName() + ")")+"&url=http://slurl.com/secondlife/"+llEscapeURL(llGetRegionName())+"/"+(string)llRound(position.x)+"/"+(string)llRound(position.y)+"/"+(string)llRound(position.z)+"&shared=yes&replace=yes&tags="+llEscapeURL(tags)+"+secondlife+slurl+squawk+gridmark+for:secondlife");
                llHTTPRequest("https://"+delicious_username+":"+delicious_password+"@api.del.icio.us/v1/tags/bundles/set", [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"], "bundle=gridmarks&tags=gridmark");
            }
    
            if(enable_magnolia) {
                llOwnerSay("Contacting Ma.gnolia ...");
                http_magnolia_post = llHTTPRequest("http://ma.gnolia.com/api/rest/1/bookmarks_add", [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"], "api_key="+magnolia_password+"&title=Second+Life:+"+llEscapeURL((string)llGetParcelDetails(position,[PARCEL_DETAILS_NAME]))+llEscapeURL(" (" + llGetRegionName() + ")")+"&description=Testing+Squawk+support+of+mag.nolia&url=http://slurl.com/secondlife/"+llEscapeURL(llGetRegionName())+"/"+(string)llRound(position.x)+"/"+(string)llRound(position.y)+"/"+(string)llRound(position.z)+"&private=0&tags="+llDumpList2String(llParseString2List(tags, [" "], []), ",")+",secondlife,slurl,squawk,squawkmark,gridmark");
            }
            
            tags = "";
            
            if(chirp) llPlaySound("Squawk Alert", 1.0);
            
        } else if((enable_delicious || enable_magnolia) && message == "/bookmark") {
            
            tags = "";
            llDialog(llGetOwner(), "Would you like to tag this bookmark? To do so, simply speak your tags in open chat. Once finished, or if you don't want to tag your link, simply press 'Finished' to create your bookmark.", ["Finished", "Cancel"], 8348729472);

            listening_tags = 1;
            llListen(0, "", llGetOwner(), "");
            llListen(8348729472, "", llGetOwner(), "Finished");
            
        }
        
    }
    
    http_response(key request_id, integer status, list metadata, string body) {
        if(request_id == http_delicious_post) {
            if(status == 415) {
                llOwnerSay("del.icio.us updated successfully.");
            } else if(status == 401) {
                llDialog(llGetOwner(), "Your del.icio.us account details appear to be incorrect. Please check your configuration and try again.", [], 1000);
            } else {
                llOwnerSay("An unknown error occured was reported while trying to update del.icio.us. The site may be unavailable for maintenance. Please try again later.");
            }
        } else if(request_id == http_magnolia_post) {
            if(status == 415) {
                llOwnerSay("Ma.gnolia updated successfully.");
            } else if(status == 401) {
                llDialog(llGetOwner(), "Your Ma.gnolia account details appear to be incorrect. Please check your configuration and try again.", [], 1000);
            } else {
                llOwnerSay("An unknown error occured was reported while trying to update Ma.gnolia. The site may be unavailable for maintenance. Please try again later.");
            }
        }
    }
    
}
