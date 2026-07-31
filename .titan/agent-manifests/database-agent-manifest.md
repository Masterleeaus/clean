# 🎯 Database Agent Manifest

**Agent Role:** Data & Database Specialist  
**Domain:** Schemas, migrations, queries, performance  
**Guild:** Backend Specialists (Agents 1, 2, 4, 5)

---

## 📋 Essential Reading
- [docs/START_HERE/AGENT_INSTRUCTIONS.md](../../docs/START_HERE/AGENT_INSTRUCTIONS.md)
- [database/migrations/](../../database/migrations/) - Migrations
- [app/Domains/*/System/Models/](../../app/Domains/) - Data models

## 🎯 Your Domain
- **Schema Design** - Create/modify tables
- **Migrations** - Database changes with rollback
- **Optimization** - Query performance, indexes
- **Relationships** - Foreign keys, constraints
- **Data Integrity** - Validation, constraints
- **Backups** - Backup strategy
- **Multi-tenancy** - company_id scoping

## 📊 Common Tasks
1. **Design schema** - Create new tables/fields
2. **Write migration** - Database changes with rollback
3. **Optimize query** - Improve performance
4. **Add index** - Speed up lookups
5. **Data migration** - Transform data safely

## ⚠️ Critical Rules
- ✅ Always scope by company_id
- ✅ Migrations reversible
- ✅ Test migrations thoroughly
- ✅ Backup before major changes
- ❌ Never lose data
- ❌ Never skip multi-tenancy

## 🔗 Related Agents
- Workcore Agent (business data)
- API Agent (data exposure)
- Migration Agent (deployment)
- Performance Agent (optimization)

## 📌 Quick Reference
**Focus:** Databases, migrations, queries  
**Key rule:** ALWAYS scope by company_id, test migrations  
**Guild:** Backend Specialists

---

**[← Back to entry](../entrance/chatgpt-start.md)**
