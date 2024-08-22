# siteadmin
 NurseStat Site Administrator
 For local environment setup, we need to introduce a local environment key in environment.json like below 
 siteadmin/config/environments.json

     "dev.nursestat.test": {
        "configfile": "devconfig.json",
        "devmode": true,
        "errorreporting": true
    }

and copy/create devconfig.json in siteadmin/config/ folder  