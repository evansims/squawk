Squawk was a popular social networking client for Second Life. It connected your ingame experience with Twitter and Jaiku for messaging and Del.icio.us and Ma.gnolia for socially "gridmarking" locations of interest inside the virtual word.

Demo
----
I've uploaded a very early and very rough demo video I threw together on [YouTube](https://www.youtube.com/watch?v=1NxKD9HmPCE).

History
-------
At peak operating times several hundered users were using Squawk simultaneously to bridge their virtual lives with these social networks.

Squawk received a lot of interest from press, as well. Mashable, ReadWriteWeb, Dr. Dobb's Journal and Information Week ran stories on the project. Dr Dobb's hosted an inworld Q&A session with myself about the project.

Warning: Sloppy Code Ahead
--------------------------
This code is very sloppy, and I cringe looking at it now. This project was purely a labor of love and a learning experience, so take it with a grain of salt. I am releasing it now as open source in the hopes that someone might learn from it or use it as a base for their own projects.

The client code will almost certainly not run out of the box today; I expect Linden has made significant changes to the scripting platform since I wrote this. I'm sure it would not be terribly difficult to get it up and running again, though.

Repo Summary
------------
/client - Contains the Lua code that runs inside Second Life.

/server - Contains the PHP backend that acted as a middleman between the Second Life clients and the social networks. It also offers a relatively simple frontend with user profiles.

/database - Contains the SQL structure necessary for the server.

