# Production Deployment Checklist for Plesk

## 1. Database Optimization

Run the performance index additions (apply once):

```bash
mysql -u sports_user -p sports_db < sql/add-performance-indexes.sql
```

Or apply individually via Plesk > Databases > phpMyAdmin:

- `idx_clubs_registration_date` on registration_date
- `idx_clubs_name` on name
- `idx_clubs_chairman_name` on chairman_name
- `idx_reorg_club_date` on (club_id, reorg_date)
- All division/location indexes

## 2. Plesk PHP Settings

**Path:** Plesk > Subscriptions > Your Domain > PHP Settings

Required changes:

```
max_execution_time     = 120 (for exports, default 30)
max_input_time         = 60 (default OK)
memory_limit           = 256M (increase from 128M for large JOINs)
post_max_size          = 10M (default OK)
upload_max_filesize    = 10M (default OK)
```

Recommended for stability:

```
default_socket_timeout = 60
mysqli.read_timeout    = 60
mysqli.connect_timeout = 10
```

## 3. MySQL Settings (via Plesk)

**Path:** Plesk > Databases > Database Management

Check/set via command line:

```sql
SHOW VARIABLES LIKE 'max_connections';     -- Should be >= 200 for concurrent users
SHOW VARIABLES LIKE 'wait_timeout';        -- Keep default 28800 (8 hours)
SHOW VARIABLES LIKE 'interactive_timeout'; -- Keep default 28800
```

If needed, create `.my.cnf` in domain root or contact Plesk support.

## 4. Caching & Sessions

- Summary cache uses `/tmp/sports_summary_cache.json` (60-second TTL)
- Verify `/tmp` is writable: Check via SSH
  ```bash
  ls -la /tmp | grep sports
  ```

## 5. SSE Client Limits

- Max 10-minute connection per client (hardcoded in summary-stream.php)
- Clients auto-disconnect after 10 min idle
- Reduces DB load from zombie connections

## 6. Export Limits Enforced

- Max 5000 rows per export (PDF/Excel)
- Max 2000 rows for `print_all` in clubs list
- Max 5000 rows for `print_all` in reports
- Returns 400 error if exceeded

## 7. Monitoring

Enable slow query logging in Plesk:

```bash
# SSH into server, then:
mysql -u admin -p -e "SET GLOBAL slow_query_log = 'ON'; SET GLOBAL long_query_time = 2;"
```

Check slow log:

```bash
tail -50 /var/log/mysql/slow.log
```

## 8. Load Testing

Test with Apache Bench before going live:

```bash
# Test 100 requests, 10 concurrent
ab -n 100 -c 10 https://your-domain.com/public/dashboard.php

# Test export endpoint
ab -n 20 -c 5 "https://your-domain.com/api/export-clubs-excel.php?limit=1000"
```

## 9. Pre-Deployment Verification

- [ ] Run `sql/add-performance-indexes.sql`
- [ ] Update PHP settings in Plesk (max_execution_time, memory_limit)
- [ ] Test dashboard loads < 3s
- [ ] Test export with 500+ rows
- [ ] Test SSE stream for 2+ minutes
- [ ] Monitor error logs: Plesk > Logs > Additional > Error Log
- [ ] Check MySQL error log: `cat /var/log/mysql/error.log`

## 10. Common Issues & Fixes

**Timeout 504 Gateway Timeout:**

- Increase PHP max_execution_time
- Verify indexes exist (check EXPLAIN PLAN)
- Reduce export row count or apply filters

**Memory Exceeded:**

- Increase PHP memory_limit to 256M
- Reduce concurrent export requests (queue them)

**High CPU from SSE:**

- Reduce poll frequency (already at 10s)
- Disable SSE if not needed
- Monitor: `ps aux | grep php`

**Slow queries:**

- Check MySQL slow log: `/var/log/mysql/slow.log`
- Run ANALYZE TABLE on all tables
- Verify index statistics are current
