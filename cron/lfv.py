#!/usr/bin/env python

import requests
import codecs

site = requests.get("https://www.aro.lfv.se/Links/Link/ViewLink?TorLinkId=308&type=MET")
if site.status_code is 200:
    try:
        f = codecs.open('"D:/vader/lfv-weather.html', 'w', 'utf-8')
        f.write(site.text)
    except:
        pass
