# TMRank

This is a small ranking project for the game Trackmania. The website [https://tmrank.jingga.app](https://tmrank.jingga.app) contains some map packs for which it generates a player ranking based on the performance of the players on these maps. The data on the website is updated once every 24h.

## Contribution

If you would like to modify the map pack or change the scoring as a more experienced Trackmania player, please feel free to create a pull request for the `maps.csv` file where all the maps and their points are stored or contact me on [discord](https://discord.com/channels/1062368297728884797/1152204810343424030). Please be considerate with what maps or map packs to add. COTD maps for example have a large player base (> 10,000 players) significantly slowing down the update process since all player records (up to 10,000 which is the Nadeo API limit) per map are checked.

### Removing maps

If a map should be removed from a map pack the [remove.csv](scripts/remove.csv) file needs to be expanded by the map + map pack type. And the map must be removed from the [map.csv](maps.csv) file.

### Manual times

If times are not available on the official trackmania leaderboards you may manually add it in the [manual.csv](scripts/manual.csv) file. Times should only be added if sufficient proof got provided that the time is legitimate.

## Api

We provide a very simple and open API. All responses are with `Content-Type: application/json;` headers.

### Types

Get all map packs:

#### Example

```
GET: https://tmrank.jingga.app/api.php?endpoint=types
```

##### Response

```json
{
    "1": {
        "type_id": 1,
        "type_name": "RPG"
    },
    "2": {
        "type_id": 2,
        "type_name": "Trial"
    },
    "3": {
        "type_id": 3,
        "type_name": "Kacky"
    },
    "6": {
        "type_id": 6,
        "type_name": "SOTD"
    }
}
```

### Ranking

Get the user ranking for a certain map type / map list / map pack

#### Request

```
GET: https://tmrank.jingga.app/api.php?endpoint=ranking&type={type_id}&offset={offset}&limit={limit}&order={order_keyword}
```

* `offset` - int / default 0
* `limit` - int / default 500 (a limit of 30,000 seems to be possible, this may require testing on your end how high you can go)
* `order` - string / default default

The `order` types supported are:
* `default` - sorts in the following order by points, fins, ats, golds, silvers, bronzes all in descending order and finally by total time in ascending order
* `finish` - sorts by finish count in descending order
* `at` - sorts by at count in descending order
* `gold` - sorts by gold count in descending order
* `silver` - sorts by silver count in descending order
* `bronze` - sorts by bronze count in descending order
* `time` - sorts by total time in **ascending** order

#### Example

```
GET: https://tmrank.jingga.app/api.php?endpoint=ranking&type=6&offset=1&limit=3&order=finish
```

##### Response

```json
{
    "e8f35258-b507-487d-b470-52587d1445a6": {
        "driver_uid": "e8f35258-b507-487d-b470-52587d1445a6",
        "driver_name": "Denniss..",
        "score": 1621,
        "fins": 158,
        "ats": 117,
        "golds": 152,
        "silvers": 153,
        "bronzes": 157,
        "ftime": 9880807,
        "rank": 2
    },
    "0fd26a9f-8f70-4f51-85e1-fe99a4ed6ffb": {
        "driver_uid": "0fd26a9f-8f70-4f51-85e1-fe99a4ed6ffb",
        "driver_name": "Schmaniol",
        "score": 1603,
        "fins": 151,
        "ats": 133,
        "golds": 146,
        "silvers": 150,
        "bronzes": 151,
        "ftime": 8290387,
        "rank": 3
    },
    "a5a111d1-1b80-46bc-af24-8f2509854333": {
        "driver_uid": "a5a111d1-1b80-46bc-af24-8f2509854333",
        "driver_name": "Maverick1357",
        "score": 1271,
        "fins": 135,
        "ats": 74,
        "golds": 95,
        "silvers": 117,
        "bronzes": 129,
        "ftime": 11335032,
        "rank": 4
    }
}
```

### Map list / Map pack

Get all maps for a certain map type / map list / map pack

#### Request

```
GET: https://tmrank.jingga.app/api.php?endpoint=maplist&type={type_id}
```

#### Example

```
GET: https://tmrank.jingga.app/api.php?endpoint=maplist&type=6
```

##### Response

```json
{
    "HDOpZb_oOgVPf2PkTdGy1nvcKs0": {
        "map_id": 389,
        "map_nid": "e72d582b-a3d6-45a5-be3f-67d141400d04",
        "map_uid": "HDOpZb_oOgVPf2PkTdGy1nvcKs0",
        "map_name": "SPAM OF THE DAY #5 ICE",
        "map_img": "https:\/\/prod.trackmania.core.nadeo.online\/storageObjects\/bf3d7584-1257-411b-911f-2bff3a616400.jpg",
        "map_finish_score": 5,
        "map_bronze_score": 6,
        "map_silver_score": 7,
        "map_gold_score": 8,
        "map_at_score": 10,
        "map_bronze_time": 19000,
        "map_silver_time": 15000,
        "map_gold_time": 13000,
        "map_at_time": 12155,
        "fins": 261,
        "wr": 10061
    },
    ...
    "qkQNqCOjuj25aQ58TN2qov6TG3n": {
        "map_id": 277,
        "map_nid": "e4170409-fea5-46e7-8199-86d6815462f5",
        "map_uid": "qkQNqCOjuj25aQ58TN2qov6TG3n",
        "map_name": "SPAM OF THE DAY #143 - Mini mi",
        "map_img": "https:\/\/prod.trackmania.core.nadeo.online\/storageObjects\/9d0df456-f18b-4c33-92a5-fc1a407e63f2.jpg",
        "map_finish_score": 5,
        "map_bronze_score": 6,
        "map_silver_score": 7,
        "map_gold_score": 8,
        "map_at_score": 10,
        "map_bronze_time": 22000,
        "map_silver_time": 18000,
        "map_gold_time": 16000,
        "map_at_time": 14454,
        "fins": 75,
        "wr": 13463
    }
}
```

### User stats

Get user stats for a certain map type / map list / map pack

#### Request

```
GET: https://tmrank.jingga.app/api.php?endpoint=userstats&type={type_id}&uid={nadeo_user_id}
```

#### Example

```
GET: https://tmrank.jingga.app/api.php?endpoint=userstats&type=6&uid=e5a9863b-1844-4436-a8a8-cea583888f8b
```

##### Response

```json
{
    "HDOpZb_oOgVPf2PkTdGy1nvcKs0": {
        "map_id": 389,
        "map_nid": "e72d582b-a3d6-45a5-be3f-67d141400d04",
        "map_uid": "HDOpZb_oOgVPf2PkTdGy1nvcKs0",
        "map_name": "SPAM OF THE DAY #5 ICE",
        "map_img": "https:\/\/prod.trackmania.core.nadeo.online\/storageObjects\/bf3d7584-1257-411b-911f-2bff3a616400.jpg",
        "map_finish_score": 5,
        "map_bronze_score": 6,
        "map_silver_score": 7,
        "map_gold_score": 8,
        "map_at_score": 10,
        "map_bronze_time": 19000,
        "map_silver_time": 15000,
        "map_gold_time": 13000,
        "map_at_time": 12155,
        "finish_id": 320734,
        "finish_driver": "e5a9863b-1844-4436-a8a8-cea583888f8b",
        "finish_map": "e72d582b-a3d6-45a5-be3f-67d141400d04",
        "finish_finish_time": 21642,
        "finish_finish_score": 5,
        "type_map_rel_id": 402,
        "type_map_rel_type": 6,
        "type_map_rel_map": "HDOpZb_oOgVPf2PkTdGy1nvcKs0",
        "fins": 21642,
        "score": 5
    },
    ...
    "xGyOppoWRMzvEiRnHkH8gwgVAtj": {
        "map_id": 386,
        "map_nid": "e24accb5-7718-4235-a5cc-e3acf7faa5a5",
        "map_uid": "xGyOppoWRMzvEiRnHkH8gwgVAtj",
        "map_name": "SPAM OF THE DAY #2 PRO MAP",
        "map_img": "https:\/\/prod.trackmania.core.nadeo.online\/storageObjects\/2788317c-23a9-4e96-82df-d0984dd151fa.jpg",
        "map_finish_score": 5,
        "map_bronze_score": 6,
        "map_silver_score": 7,
        "map_gold_score": 8,
        "map_at_score": 10,
        "map_bronze_time": 46000,
        "map_silver_time": 37000,
        "map_gold_time": 33000,
        "map_at_time": 30462,
        "finish_id": 319409,
        "finish_driver": "e5a9863b-1844-4436-a8a8-cea583888f8b",
        "finish_map": "e24accb5-7718-4235-a5cc-e3acf7faa5a5",
        "finish_finish_time": 29561,
        "finish_finish_score": 10,
        "type_map_rel_id": 399,
        "type_map_rel_type": 6,
        "type_map_rel_map": "xGyOppoWRMzvEiRnHkH8gwgVAtj",
        "fins": 29561,
        "score": 10
    }
}
```

### Find a user id

Find a username in the database. This search is global and not restricted by map pack / map type.

#### Request

```
GET: https://tmrank.jingga.app/api.php?endpoint=finduser&name={user_name}
```

#### Example

```
GET: https://tmrank.jingga.app/api.php?endpoint=finduser&name=spam
```

##### Response

```json
{
    "3bb0d130-637d-46a6-9c19-87fe4bda3c52": {
        "driver_uid": "3bb0d130-637d-46a6-9c19-87fe4bda3c52",
        "driver_name": "Spammiej"
    },
    "988bc813-b3aa-496e-bd06-1afc45c2c65a": {
        "driver_uid": "988bc813-b3aa-496e-bd06-1afc45c2c65a",
        "driver_name": "spammossiej"
    },
    "15fa4146-df69-4a84-b8e0-76675da720fe": {
        "driver_uid": "15fa4146-df69-4a84-b8e0-76675da720fe",
        "driver_name": "Project_SadSpam"
    },
    "2f88da13-0d2f-4c64-ac08-2c97f4ac4a88": {
        "driver_uid": "2f88da13-0d2f-4c64-ac08-2c97f4ac4a88",
        "driver_name": "DrSpamoman"
    },
    "2fedbb68-71e7-4c39-838c-2d5d0dfc5cfc": {
        "driver_uid": "2fedbb68-71e7-4c39-838c-2d5d0dfc5cfc",
        "driver_name": "Spamo-"
    },
    "d18b4df3-9294-416f-ba54-efb4bca55482": {
        "driver_uid": "d18b4df3-9294-416f-ba54-efb4bca55482",
        "driver_name": "Jamspammer2021"
    }
}
```

### Global user data

Get application wide user data. This api endpoint is slow as it calculates the ranking live for every map type / map pack. Try to avoid this api request.

#### Request

```
GET: https://tmrank.jingga.app/api.php?endpoint=user&uid={nadeo_user_id}
```

#### Example

```
GET: https://tmrank.jingga.app/api.php?endpoint=user&uid=e8f35258-b507-487d-b470-52587d1445a6
```

##### Response

```json
{
    "driver_uid": "e8f35258-b507-487d-b470-52587d1445a6",
    "driver_name": "Denniss..",
    "types": {
        "1": {
            "type_id": 1,
            "type_name": "RPG",
            "score": 336,
            "fins": 24,
            "ats": 9,
            "golds": 13,
            "silvers": 21,
            "bronzes": 22,
            "ftime": 23711517,
            "rank": 1112
        },
        ...
        "10": {
            "type_id": 10,
            "type_name": "TMFL",
            "score": 287,
            "fins": 35,
            "ats": 8,
            "golds": 26,
            "silvers": 35,
            "bronzes": 35,
            "ftime": 6812389,
            "rank": 95
        }
    }
}
```
