# Trackmania Ranking

This is a small ranking project for the game Trackmania. The website [https://tmrank.jingga.app](https://tmrank.jingga.app) contains some map packs for which it generates a player ranking based on the performance of the players on these maps. The data on the website is updated once every 24h.

## Contribution

If you would like to modify the map pack or change the scoring as a more experienced Trackmania player, please feel free to create a pull request for the `maps.csv` file where all the maps and their points are stored.

## Api

We provide a very simple and open API

### Types

Get all map packs:

#### Example

```
https://tmrank.jingga.app/api.php?endpoint=types
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
https://tmrank.jingga.app/api.php?endpoint=ranking&type={type_id}&offset={offset}&limit={limit}
```

* `offset` - int / default 0
* `limit` - int / default 500

#### Example

```
https://tmrank.jingga.app/api.php?endpoint=ranking&type=6&offset=1&limit=3
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
https://tmrank.jingga.app/api.php?endpoint=maplist&type={type_id}
```

#### Example

```
https://tmrank.jingga.app/api.php?endpoint=maplist&type=6
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


