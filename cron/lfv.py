#!/usr/bin/env python

import requests

site = requests.get("https://www.aro.lfv.se/Links/Link/ViewLink?TorLinkId=308&type=MET")

f = open('D:/vader/lfv-weather.html', 'w')
f.write(site.text)
