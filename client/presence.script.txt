integer need_reset = 0;

key owner_key;
string owner_name = "";
string owner_name_escaped = "";

string last_message = "";
integer hide_replies = 0;

integer chirp = 0;
integer unlocked = 2;
integer channel = 42;

integer enable_squawknest = 0;
integer enable_twitter = 0;
integer enable_jaiku = 0;
integer enable_notifications = 0;
integer max_notifications = 10;

string twitter_username = "";
string twitter_password = "";
integer twitter_geocode = 0;

string jaiku_username = "";
string jaiku_password = "";
integer jaiku_geocode = 0;
integer jaiku_location = 0;

string delicious_username = "";
string magnolia_username = "";

key data_config = NULL_KEY;
integer data_config_line = 0;

key http_notifications = NULL_KEY;
key http_twitter_post = NULL_KEY;
key http_jaiku_post = NULL_KEY;
key http_donator = NULL_KEY;

integer presenceUpdate(string message) {

    vector position = llGetPos();
    string geocode = "SL:" + llGetRegionName() + "(" + (string)llRound(position.x) + "," + (string)llRound(position.y) + "," + (string)llRound(position.z) + ")";

    if(enable_twitter) {
        llOwnerSay("Contacting Twitter ...");
        if(twitter_geocode) {
            if((llStringLength(message) + llStringLength(geocode) + 4) > 160) {
                http_twitter_post = llHTTPRequest("http://"+twitter_username+":"+twitter_password+"@twitter.com/statuses/update.xml?status="+llEscapeURL(llGetSubString(message, 0, (llStringLength(message) - llStringLength(geocode) - 3)) + "... " + geocode), [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"], "");
            } else {
                http_twitter_post = llHTTPRequest("http://"+twitter_username+":"+twitter_password+"@twitter.com/statuses/update.xml?status="+llEscapeURL(message + " " + geocode), [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"], "");
            }
        } else {
            http_twitter_post = llHTTPRequest("http://"+twitter_username+":"+twitter_password+"@twitter.com/statuses/update.xml?status="+llEscapeURL(message), [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"], "");
        }
    }

    if(enable_jaiku) {
        llOwnerSay("Contacting Jaiku ...");
        if(jaiku_location) {
            if(jaiku_geocode) {
                http_jaiku_post = llHTTPRequest("http://api.jaiku.com/json", [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"],"user="+jaiku_username+"&personal_key="+jaiku_password+"&method=presence.send&message="+llEscapeURL(message)+"&location=Second+Life+at+"+(string)llRound(position.x)+","+(string)llRound(position.y)+"+in+"+llEscapeURL(llGetRegionName()));
            } else {
                http_jaiku_post = llHTTPRequest("http://api.jaiku.com/json", [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"],"user="+jaiku_username+"&personal_key="+jaiku_password+"&method=presence.send&message="+llEscapeURL(message)+"&location=Second+Life");
            }
        } else if(jaiku_geocode) {
            if((llStringLength(message) + llStringLength(geocode) + 4) > 140) {
                http_jaiku_post = llHTTPRequest("http://api.jaiku.com/json", [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"],"user="+jaiku_username+"&personal_key="+jaiku_password+"&method=presence.send&message="+llEscapeURL(llGetSubString(message, 0, (llStringLength(message) - llStringLength(geocode) - 3)) + "... " + geocode));
            } else {
                http_jaiku_post = llHTTPRequest("http://api.jaiku.com/json", [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"],"user="+jaiku_username+"&personal_key="+jaiku_password+"&method=presence.send&message="+llEscapeURL(message + " " + geocode));
            }
        } else {
            http_jaiku_post = llHTTPRequest("http://api.jaiku.com/json", [HTTP_METHOD,"POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"],"user="+jaiku_username+"&personal_key="+jaiku_password+"&method=presence.send&message="+llEscapeURL(message));
        }
    }

    if(enable_squawknest) llHTTPRequest("http://www.squawknest.com/api/update.php?name=" + owner_name_escaped + "&region=" + llEscapeURL(llGetRegionName()) + "&x=" + (string)llRound(position.x) + "&y=" + (string)llRound(position.y) + "&z=" + (string)llRound(position.z) + "&message=" + llEscapeURL(message), [HTTP_METHOD,"POST"], "");
    if(chirp) llPlaySound("Squawk Alert", 1.0);

    return 1;

}

default {

    changed(integer mask) {
        if(llGetAttached() != 0) {
             if(mask & CHANGED_OWNER) { llResetScript(); }
            if(mask & CHANGED_INVENTORY) { llResetScript(); }
        }
    }
    on_rez(integer param) { llResetScript(); }

    state_entry() {

        if(need_reset) { llResetScript(); }

        llSetAlpha(1.0,ALL_SIDES);

        if(llGetAttached() != 0) {

            need_reset = 1;

            if(llGetAttached() > 30) llSetAlpha(0.0,ALL_SIDES);

            llListenRemove(channel);

            owner_key = llGetOwner();
            owner_name = llKey2Name(owner_key);
            owner_name_escaped = llEscapeURL(owner_name);

            llOwnerSay("Loading...");
            http_donator = llHTTPRequest("http://www.squawknest.com/api/donator_14.php?name=" + owner_name_escaped, [HTTP_METHOD, "GET"], "");
            llSetTimerEvent(10.0); // Ensure that if the site times out we still continue to setup stage.

        }

    }

    timer() {
        if(unlocked > 1) {
            unlocked = 0;
            llSetTimerEvent(0.0);
            llOwnerSay("Unable to contact Squawk database; donator mode temporarily disabled.");
            data_config = llGetNotecardLine("Configuration", data_config_line);
        }
    }

    http_response(key request_id, integer status, list metadata, string body) {
        if(request_id == http_donator) {
            if(unlocked > 1) {
                llSetTimerEvent(0.0);
                if(status == 200) { unlocked = 1; llOwnerSay("Thank you for your support! All features have been unlocked."); } else { unlocked = 0; }
                data_config = llGetNotecardLine("Configuration", data_config_line);
            }
        }
    }

    dataserver(key query_id, string data) {

        if(query_id != data_config) return;

        if(data == EOF) {
            
            data_config = NULL_KEY;

            if(enable_twitter && (twitter_username == "" || twitter_password == "")) {
                llDialog(owner_key, "If you wish to enable Twitter support you must provide your username and password. Please check your Configuration card.", [], 1000);
            } else if(enable_notifications && !unlocked) {
                llDialog(owner_key, "Friend notifications are for donators only. Please visit any of our Squawk tip jars and donate L$200 or more to gain access to this feature.", [], 1000);
            } else if(enable_twitter && ~llSubStringIndex(twitter_username, "@")) {
                llDialog(owner_key, "Your Twitter username appears to be incorrect. You've used your email address rather than your username, which will not work. Please update your configuration with the proper username.", [], 1000);
            } else if(enable_jaiku && (jaiku_username == "" || jaiku_password == "")) {
                llDialog(owner_key, "If you wish to enable Jaiku support you must provide your username and API key. Please check your Configuration card.", [], 1000);
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

            if(llGetSubString(setting, 0, 6) == "twitter") {
                if(setting == "twitter") {
                    enable_twitter = boolvalue;
                } else if(setting == "twitter username") {
                    twitter_username = llEscapeURL(value);
                } else if(setting == "twitter password") {
                    twitter_password = llEscapeURL(value);
                } else if(setting == "twitter geocode") {
                    twitter_geocode = boolvalue;
                }
            } else if(llGetSubString(setting, 0, 4) == "jaiku") {
                if(setting == "jaiku") {
                    enable_jaiku = boolvalue;
                } else if(setting == "jaiku username") {
                    jaiku_username = llEscapeURL(value);
                } else if(setting == "jaiku api key") {
                    jaiku_password = llEscapeURL(value);
                } else if(setting == "jaiku geocode") {
                    jaiku_geocode = boolvalue;
                } else if(setting == "jaiku location") {
                    jaiku_location = boolvalue;
                }
            } else if(setting == "delicious username") {
                delicious_username = value;
            } else if(setting == "magnolia username") {
                magnolia_username = value;
            } else if(setting == "chirp") {
                chirp = boolvalue;
            } else if(setting == "squawknest") {
                enable_squawknest = boolvalue;
            } else if(setting == "channel") {
                channel = (integer)value;
            } else if(setting == "notifications") {
                enable_notifications = boolvalue;
            } else if(setting == "max notifications per update") {
                max_notifications = (integer)value;
                if(max_notifications > 10) max_notifications = 10;
                if(max_notifications < 1) max_notifications = 1;
            } else if(setting == "hide responses to others") {
                hide_replies = boolvalue;
            } else if(setting == "configured") {
                if(!boolvalue) {
                    llDialog(owner_key, "Thanks for chosing Squawk! Please see the 'Read Me' notecard for instructions on getting started.", [], 1000);
                    llOwnerSay("Please edit the 'Configuration' notecard inside Squawk to begin.");
                    return;
                }
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

    state_entry() {
        llListen(channel, "", owner_key, "");
        llListen(3284730, "", owner_key, "Continue");

        llOwnerSay("To send a presence update, type: /" + (string)channel + " followed by your message.");

        llSetTimerEvent(1.0);
    }

    touch(integer touched){
        if(llDetectedKey(0) == owner_key) {

            list uiButtons;
            
            if(delicious_username) uiButtons = ["del.icio.us"] + uiButtons;
            if(magnolia_username) uiButtons = ["Mag.olia"] + uiButtons;

            if(enable_twitter) uiButtons = ["Twitter"] + uiButtons;
            if(enable_jaiku) uiButtons = ["Jaiku"] + uiButtons;
            if(enable_squawknest) uiButtons = ["Squawknest"] + uiButtons;

            uiButtons = ["Help", "Share"] + uiButtons;

            llListenRemove(3284729);
            llListen(3284729, "", owner_key, "");
            llDialog(owner_key, "Squawk\n\nUse the options below to access your configured web services.", uiButtons, 3284729);

        }
    }

    sensor(integer total_number) {
        if(llGetAgentInfo(owner_key) & AGENT_AWAY) {
        } else if(llGetAgentInfo(owner_key) & AGENT_BUSY) {
        } else {
            llShout(3284728, "Squawk/" + owner_name);
        }
    }

    timer() {
        llSetTimerEvent(0.0);
        if(enable_notifications == 1 || enable_squawknest) {
            if(llGetAgentInfo(owner_key) & AGENT_AWAY) {
            } else if(llGetAgentInfo(owner_key) & AGENT_BUSY) {
            } else {
                if(enable_squawknest == 1) llHTTPRequest("http://www.squawknest.com/api/update.php?heartbeat&name=" + owner_name_escaped + "&key=" + (string)owner_key + "&twitter=" + twitter_username + "&jaiku=" + jaiku_username + "&delicious" + delicious_username + "&magnolia=" + magnolia_username, [HTTP_METHOD,"POST"], "");
                if(unlocked == 1 && (enable_notifications == 1 || enable_notifications == 3)) {
                    if(enable_notifications == 3) {
                        enable_notifications = 1;
                        http_notifications = llHTTPRequest("http://www.squawknest.com/api/friends_14.php?name=" + owner_name_escaped + "&t=" + twitter_username + "&tp=" + twitter_password + "&j=" + jaiku_username + "&jp=" + jaiku_password + "&hidereplies=" + (string)hide_replies + "&maxcount=" + (string)max_notifications, [HTTP_METHOD,"GET"], "");
                    } else {
                        enable_notifications = 3;
                    }
                }
            }
            llSetTimerEvent(180.0);
        }
    }

    listen(integer incoming_channel, string name, key id, string message) {

        message = llStringTrim(message, STRING_TRIM);

        if(id == owner_key) {
            if(incoming_channel == 3284729) {
                if(message == "Squawknest") {
                    llLoadURL(llGetOwner(), "View your Squawk Profile.", "http://www.squawknest.com/people?name=" + owner_name_escaped);
                } else if(message == "Twitter") {
                    llLoadURL(llGetOwner(), "View your Twitter Profile.", "http://twitter.com/" + twitter_username);
                } else if(message == "Jaiku") {
                    llLoadURL(llGetOwner(), "View your Jaiku Profile.", "http://" + jaiku_username + ".jaiku.com/");
                } else if(message == "del.icio.us") {
                    llLoadURL(llGetOwner(), "View your del.icio.us Profile.", "http://del.icio.us/" + delicious_username);
                } else if(message == "Ma.gnolia") {
                    llLoadURL(llGetOwner(), "View your Ma.gnolia Profile.", "http://ma.gnolia.com/people/" + magnolia_username);
                } else if(message == "Help") {
                    llLoadURL(llGetOwner(), "Visit the Squawk support forums.", "http://www.squawknest.com/support/");
                } else if(message == "Share") {
                    llSay(0, llKey2Name(llGetOwner()) + " uses the following web services:");
                    if(enable_squawknest) llSay(0, "Squawk: http://www.squawknest.com/people?name="+owner_name_escaped);
                    if(enable_jaiku) llSay(0, "Jaiku: http://" + jaiku_username + ".jaiku.com");
                    if(enable_twitter) llSay(0, "Twitter: http://www.twitter.com/" + twitter_username);
                    if(delicious_username) llSay(0, "del.icio.us: http://del.icio.us/" + delicious_username);
                    if(magnolia_username) llSay(0, "Ma.gnolia: http://ma.gnolia.com/people/" + magnolia_username);
                }
                llListenRemove(3284729);
            } else if(incoming_channel == 3284730 && message == "Continue") {
                presenceUpdate(last_message);
            } else if(incoming_channel == channel) {
                integer charlimit = 160;
                if(enable_jaiku) charlimit = 140;
                
                if(llStringLength(message) > charlimit) {
                    llDialog(owner_key, "Your message is a bit long, and may have to be trimmed down to accomodate limits imposed by your presence services. Do you still wish to post this message?", ["Continue", "Cancel"], 3284730);
                    last_message = llGetSubString(message, 0, (charlimit - 4));
                    llOwnerSay("If you continue, your message (not including geocode, if enabled) will read something like this:\n" + last_message + "...");
                } else {
                    presenceUpdate(message);
                }
            }
        }

    }

    http_response(key request_id, integer status, list metadata, string body) {
        if(request_id == http_twitter_post) {
            if(status == 415) {
                llOwnerSay("Twitter updated successfully.");
            } else if(status == 401) {
               llDialog(owner_key, "Your Twitter account details appear to be incorrect. Please check your configuration and try again.", [], 1000);
            } else {
                llOwnerSay("An unknown error occured was reported while trying to update Twitter. The site may be unavailable for maintenance. Please try again later.");
            }
        } else if(request_id == http_jaiku_post) {
            if(status == 200) {
                llOwnerSay("Jaiku updated successfully.");
            } else if(status == 302) {
                llDialog(owner_key, "Your Jaiku account details appear to be incorrect. Please check your configuration and try again.", [], 1000);
            } else {
                llOwnerSay("An unknown error occured was reported while trying to update Jaiku. The site may be unavailable for maintenance. Please try again later.");
            }
        } else if(request_id == http_notifications && unlocked == 1) {
            if(status == 200 || status == 206) {
                llOwnerSay(body);
                if(status == 206) {
                    if(enable_notifications == 1) { enable_notifications = 2; } // Temporarily disable automated presence checkins while we get our queue.
                    llSleep(0.1);
                    http_notifications = llHTTPRequest("http://www.squawknest.com/api/friends_14.php?name=" + owner_name_escaped + "&t=" + twitter_username + "&tp=" + twitter_password + "&j=" + jaiku_username + "&jp=" + jaiku_password, [HTTP_METHOD,"GET"], "");
                    return;
                } else if(enable_notifications == 2) { enable_notifications = 1; }
            } else if(status == 401) {
                enable_notifications = 0;
            }
        }
    }

}
