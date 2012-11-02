string prod_vers = "1.4";
string upgrade_server = "56e4026a-aaa9-79be-ea74-49664a2b3793";

default
{

    changed(integer mask) {
        if(llGetAttached() != 0) {
             if(mask & CHANGED_OWNER) { llResetScript(); }
            if(mask & CHANGED_INVENTORY) { llResetScript(); }
        }
    }
    on_rez(integer param) { llResetScript(); }
    
    state_entry() {
        llEmail(upgrade_server + "@lsl.secondlife.com", "Squawk/" + (string)prod_vers, (string)llGetOwner());
    }

}
