int main(void)
{
    void *ptr1,*ptr2,*ptr3,*ptr4;
    ptr1=malloc(128);//smallbin1
    ptr2=malloc(0x10);//fastbin1
    ptr3=malloc(0x10);//fastbin2
    ptr4=malloc(128);//smallbin2
    malloc(0x10);//防止与top合并
    free(ptr1);
    *(int *)((long long)ptr4-0x8)=0x90;//修改pre_inuse域
    *(int *)((long long)ptr4-0x10)=0xd0;//修改pre_size域
    free(ptr4);//unlink进行前向extend
    malloc(0x150);//占位块

}
