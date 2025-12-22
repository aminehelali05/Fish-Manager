#!/usr/bin/env bash
# set_railway_vars.sh
# Use this script locally after `railway login` && `railway link`
# WARNING: This will expose DB password in your shell history. Prefer setting via Railway dashboard.

railway variables set DB_HOST mysql.railway.internal
railway variables set DB_USER root
railway variables set DB_PASS CuTdhwVTfeNucsgXYbOHVWYvbqJvmsgZ
railway variables set DB_NAME railway
# Set an import token; replace with your own secret
railway variables set IMPORT_TOKEN s3cure-IMPORT-T0KEN-9f3c6a2b7d4e

echo "Railway variables set. Verify with: railway variables"
