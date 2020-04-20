namespace php Services.Test
service Test
{
	string say(1:string name);
	list<map<string,string>> getlistMap(1:list<map<string,string>> listmap);
}