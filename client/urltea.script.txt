key http_url_tea = NULL_KEY;
string url = "";

default
{
    state_entry()
    {
        llListen(0, "", llGetOwner(), "");
    }

    listen(integer incoming_channel, string name, key id, string message) {
        
        if(id == llGetOwner() && incoming_channel == 0 && llStringLength(message) > 5 && llGetSubString(message, 0, 7) == "/urltea ") {
            url = llStringTrim(llGetSubString(message, 7, -1), STRING_TRIM);
            if(llStringLength(url) > 3) {
                if(llGetSubString(url, 0, 6) == "http://") {
                    url = llGetSubString(url, 7, -1);
                }
                url = llEscapeURL(url);
                http_url_tea = llHTTPRequest("http://urltea.com/api/text/?url="+url, [HTTP_METHOD,"GET"], "");
            }
        }
        
    }
    
    http_response(key request_id, integer status, list metadata, string body) {
        if(request_id == http_url_tea) {
            if(status == 200) {
                llOwnerSay("Short URL for '" + url + "': \n" + llStringTrim(body, STRING_TRIM));
            } else {
                llOwnerSay("urlTea couldn't shorten '" + url + '". Please ensure that this is a properly formatted URL and try again.");
            }
        }
    }
    
}
