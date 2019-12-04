int main(void)
{
    char buffer[40]="";
    void *chunk1;
    chunk1=malloc(24);
    puts("Get Input");
    gets(buffer);
    if(strlen(buffer)==33)
    {
        strcpy(chunk1,buffer);
    }
    return 0;

}
