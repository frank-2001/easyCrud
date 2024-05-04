class DB{
    get(line){
        return JSON.parse(localStorage.getItem(line))
    }
    set(line,data){
            localStorage.setItem(line,JSON.stringify(data))
            return this.get(line)
    }
    add(line,data){
        let old=this.get(line)
        old.push(data)
        localStorage.setItem(line,JSON.stringify(old))
        return this.get(line)
    }
    concat(line,data){
        let old=this.get(line)
        let new_data=old.concat(data)
        localStorage.setItem(line,JSON.stringify(new_data))
        return this.get(line)
    }
    remove(line){
        localStorage.removeItem(line)
    }
}
const  db = new DB();