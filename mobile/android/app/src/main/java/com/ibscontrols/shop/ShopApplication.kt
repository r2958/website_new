package com.ibscontrols.shop

import android.app.Application

class ShopApplication : Application() {
    
    companion object {
        lateinit var instance: ShopApplication
            private set
    }
    
    override fun onCreate() {
        super.onCreate()
        instance = this
    }
}
