# Bitcoin Price Logger
# Author: Zach Gillis

import requests
import mysql.connector
import time
import datetime

def getBTCPrice():
    # API URL for Bitcoin price
    URL = "https://api.coindesk.com/v1/bpi/currentprice/BTC.json"

    price = None

    try:
        print("Requesting BTC/USD price...")
        r = requests.get(URL)
        data = r.json()

        price = data['bpi']['USD']['rate']
        price = float(price.replace(',', ''))

    except:
        print("Failed to retrieve BTC price.")

    return price