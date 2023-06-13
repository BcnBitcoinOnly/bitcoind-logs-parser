# bitcoind-logs-parser


## Preparations

Add this configuration line to your `bitcoin.conf` and restart bitcoind:

```
debug=cmpctblock
```

This will prompt your node to start writing extra information in the `debug.log`.
For each new block the node receives, it should go from printing this:

```
2023-06-08T02:23:25Z UpdateTip: new best=000000000000000000001c46dda7e778f4fa7598bee48dd94e898d119fa7484e height=793329 version=0x246e8000 log2_work=94.228102 tx=849304858 date='2023-06-08T02:23:16Z' progress=1.000000 cache=5.1MiB(29267txo)
```

To this:

```
2023-06-08T02:23:24Z Initialized PartiallyDownloadedBlock for block 000000000000000000001c46dda7e778f4fa7598bee48dd94e898d119fa7484e using a cmpctblock of size 15535
2023-06-08T02:23:25Z Successfully reconstructed block 000000000000000000001c46dda7e778f4fa7598bee48dd94e898d119fa7484e with 1 txn prefilled, 1423 txn from mempool (incl at least 55 from extra pool) and 1115 txn requested
2023-06-08T02:23:25Z UpdateTip: new best=000000000000000000001c46dda7e778f4fa7598bee48dd94e898d119fa7484e height=793329 version=0x246e8000 log2_work=94.228102 tx=849304858 date='2023-06-08T02:23:16Z' progress=1.000000 cache=5.1MiB(29267txo)
```


## Usage

```bash
$ php parser.php path/to/debug.log > table.csv
```


## Sample

```
timestamp,height,hash,version,log2_work,compact_size_bytes,prefilled_txs,mempool_txs,extra_mempool_txs,requested_txs,validation_time
2023-06-07T00:16:14Z,793180,0000000000000000000097f32391da9fc1870759eaadb52965ae30f4a3202732,0x328de000,94.226061,15724,1,2392,0,162,1
2023-06-07T00:16:58Z,793181,00000000000000000002ada0c351f0699b916d4ea52b853918b10920b7c68f2f,0x31070000,94.226075,13507,1,2022,2,163,1
2023-06-07T00:26:45Z,793182,000000000000000000006c2421ee6f25e1b32e50cbcc1544548a4981e9eae2fd,0x318d8000,94.226089,23287,1,3758,51,72,1
2023-06-07T00:28:36Z,793183,000000000000000000026ec9d67064ba963ff29dc9bdbffaa0bdb5faaba897f6,0x20000000,94.226102,25928,1,4077,7,186,1
2023-06-07T00:34:19Z,793184,0000000000000000000538cde55a4a3e11bce13f9b467519962b2bafde091fb8,0x201c6000,94.226116,30915,1,3110,53,1976,4
2023-06-07T01:00:44Z,793185,00000000000000000000b5b8e7d0aa7b35e3b760f438cdc35c92b66b45e0d0ea,0x2e52e000,94.226130,15901,1,2473,1,126,1
2023-06-07T01:07:08Z,793186,00000000000000000003641844b8bc4f53141332366b0283478935657220b00b,0x20008000,94.226143,23575,1,2415,5,1448,2
```

Note: even with `debug=cmpctblock` turned on some debug lines can be missing for some of the blocks.
When the script doesn't find all the information it needs to fill in all columns it will print `NULL`, like so:

```
2023-06-12T13:50:33Z,794041,00000000000000000002bffdb280d5792665fc8b5e58f2bb6a2bd794fe9950de,0x20200000,94.237812,14293,NULL,NULL,NULL,NULL,40
```
