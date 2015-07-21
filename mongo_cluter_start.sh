#!/bin/bash
/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/cfg/node0/data
/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/cfg/node1/data
/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/cfg/node2/data

/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/rs0/node0/data
/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/rs0/node1/data
/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/rs0/node2/data

/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/rs1/node0/data
/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/rs1/node1/data
/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/rs1/node2/data

/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/rs2/node0/data
/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/rs2/node1/data
/usr/local/mongodb/bin/mongod --shutdown --dbpath /data/mongo/rs2/node2/data

#shutdown finish

su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/cfg/node0/cnf/cfg.conf"
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/cfg/node1/cnf/cfg.conf"
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/cfg/node2/cnf/cfg.conf"

sleep 5s
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/rs0/node0/cnf/mongod.conf"
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/rs0/node1/cnf/mongod.conf"
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/rs0/node2/cnf/mongod.conf"

sleep 2s
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/rs1/node0/cnf/mongod.conf"
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/rs1/node1/cnf/mongod.conf"
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/rs1/node2/cnf/mongod.conf"

sleep 2s
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/rs2/node0/cnf/mongod.conf"
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/rs2/node1/cnf/mongod.conf"
su mongod -fm -c "/usr/local/mongodb/bin/mongod --fork --config /data/mongo/rs2/node2/cnf/mongod.conf"

sleep 10s
su mongod -fm -c "/usr/local/mongodb/bin/mongos --fork --config /data/mongo/mongos/cnf/mongos.conf"
