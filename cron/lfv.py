#!/usr/bin/env python

import requests

site = requests.get("https://www.aro.lfv.se/Links/Link/ViewLink?TorLinkId=308&type=MET")
if site.status_code is 200:
    try:
        f = open('D:/vader/lfv-weather.html', 'w')
        f.write(site.text)
    except:
        continue
