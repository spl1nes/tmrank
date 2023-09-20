# Trackmania Ranking

This is a small ranking project for the game Trackmania. The website [https://tmrank.jingga.app](https://tmrank.jingga.app) contains some map packs for which it generates a player ranking based on the performance of the players on these maps. The data on the website is updated once every 24h.

## Contribution

If you would like to modify the map pack or change the scoring as a more experienced Trackmania player, please feel free to create a pull request for the `maps.csv` file where all the maps and their points are stored.

## Api

We provide a very simple and open API. All responds are with `Content-Type: application/json;` headers.

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
* `limit` - int / default 500
* `order` - string / default default

The `order` types supported are:
* `default` - sorts by points, fins, ats, golds, silvers, bronzes all in descending order and finally by total time in ascending order
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
    "0fd26a9f-8f70-4f51-85e1-fe99a4ed6ffb": {
        "driver_uid": "0fd26a9f-8f70-4f51-85e1-fe99a4ed6ffb",
        "driver_name": "Schmaniol",
        "score": 1603,
        "fins": 151,
        "ats": 133,
        "golds": 146,
        "silvers": 150,
        "bronzes": 151,
        "ftime": 8290387
    },
    "f3ee4980-148c-4151-842a-061d107554eb": {
        "driver_uid": "f3ee4980-148c-4151-842a-061d107554eb",
        "driver_name": "Omumm",
        "score": 1324,
        "fins": 125,
        "ats": 104,
        "golds": 118,
        "silvers": 122,
        "bronzes": 124,
        "ftime": 6454173
    },
    "d023a015-c715-4fb8-8d6a-560a6553417e": {
        "driver_uid": "d023a015-c715-4fb8-8d6a-560a6553417e",
        "driver_name": "GrdAlf",
        "score": 1296,
        "fins": 163,
        "ats": 33,
        "golds": 64,
        "silvers": 116,
        "bronzes": 152,
        "ftime": 12674098
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

