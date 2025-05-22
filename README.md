SolArk publishes no information about an API for the data going to the cloud from their inverters. So I managed to figure one out using information in the PowerView web app they provide their customers.  The data in that app is not real-time and has a granularity measured in minutes, but this is good enough for lots of automation needs, such as deciding when large loads should be turned off or on. The number of watts being collected from a panel is also a good proxy for whether the sun is out or not, and that can be used to decide to turn on a pump for, say, hydronic hot water collection.  SunSynk uses very similar technology (and had even been using the same backend) so people with SunSynk inverters might also find this useful.  Later I managed to collect data directly from the inverter after poring over the data communicated from a serial port on the WiFi dongle. See that here:

https://github.com/judasgutenberg/SolArk_Live_Data

I've already included this and the live-data API in parts of my ESP8266-base Remote Control project.  See https://github.com/judasgutenberg/Esp8266_RemoteControl.

Update June 7, 2024: SolArk migrated their backend from PowerView to their own domain at Solarkcloud.com.  The API was otherwise unchanged, and was easy to change in this code. It also uncovered some embarrassing hardcoded bits I'd overlooked.
