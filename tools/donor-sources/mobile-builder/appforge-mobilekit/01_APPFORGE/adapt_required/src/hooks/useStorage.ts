/**
 * useStorage Hook
 * 
 * Provides easy file upload/download functionality using Supabase Storage.
 * Supports multiple bucket types with proper access control.
 */

import { useState, useCallback } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { useAuth } from '@/contexts/AuthContext';
import { useToast } from '@/hooks/use-toast';

export type StorageBucket = 'avatars' | 'app-icons' | 'splash-screens' | 'apk-builds' | 'project-assets';

interface UploadOptions {
  bucket: StorageBucket;
  path?: string; // Custom path within user folder
  onProgress?: (progress: number) => void;
  maxSizeMB?: number;
  allowedTypes?: string[];
}

interface UploadResult {
  url: string;
  path: string;
  error: Error | null;
}

interface UseStorageReturn {
  upload: (file: File, options: UploadOptions) => Promise<UploadResult>;
  remove: (bucket: StorageBucket, path: string) => Promise<{ error: Error | null }>;
  getPublicUrl: (bucket: StorageBucket, path: string) => string;
  getSignedUrl: (bucket: StorageBucket, path: string, expiresIn?: number) => Promise<string | null>;
  listFiles: (bucket: StorageBucket, folder?: string) => Promise<{ name: string; url: string }[]>;
  isUploading: boolean;
  uploadProgress: number;
}

// File type validation
const DEFAULT_ALLOWED_TYPES: Record<StorageBucket, string[]> = {
  'avatars': ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
  'app-icons': ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'],
  'splash-screens': ['image/png', 'image/jpeg', 'image/webp'],
  'apk-builds': ['application/vnd.android.package-archive', 'application/octet-stream', 'application/zip'],
  'project-assets': ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml', 'application/pdf', 'application/json'],
};

// Max file sizes in MB
const DEFAULT_MAX_SIZES: Record<StorageBucket, number> = {
  'avatars': 5,
  'app-icons': 10,
  'splash-screens': 10,
  'apk-builds': 500,
  'project-assets': 50,
};

export function useStorage(): UseStorageReturn {
  const { user } = useAuth();
  const { toast } = useToast();
  const [isUploading, setIsUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(0);

  const upload = useCallback(async (file: File, options: UploadOptions): Promise<UploadResult> => {
    const { bucket, path, maxSizeMB, allowedTypes } = options;
    
    if (!user) {
      return { url: '', path: '', error: new Error('You must be logged in to upload files') };
    }

    // Validate file type
    const allowedMimeTypes = allowedTypes || DEFAULT_ALLOWED_TYPES[bucket];
    if (!allowedMimeTypes.includes(file.type)) {
      const error = new Error(`File type not allowed. Accepted: ${allowedMimeTypes.join(', ')}`);
      toast({
        title: 'Upload Failed',
        description: error.message,
        variant: 'destructive',
      });
      return { url: '', path: '', error };
    }

    // Validate file size
    const maxSize = (maxSizeMB || DEFAULT_MAX_SIZES[bucket]) * 1024 * 1024;
    if (file.size > maxSize) {
      const error = new Error(`File too large. Max size: ${maxSizeMB || DEFAULT_MAX_SIZES[bucket]}MB`);
      toast({
        title: 'Upload Failed',
        description: error.message,
        variant: 'destructive',
      });
      return { url: '', path: '', error };
    }

    setIsUploading(true);
    setUploadProgress(0);

    try {
      // Generate unique filename
      const fileExt = file.name.split('.').pop();
      const timestamp = Date.now();
      const randomStr = Math.random().toString(36).substring(2, 8);
      const fileName = path 
        ? `${path}/${timestamp}-${randomStr}.${fileExt}`
        : `${timestamp}-${randomStr}.${fileExt}`;
      
      // Path includes user ID for RLS policies
      const fullPath = `${user.id}/${fileName}`;

      // Upload file
      const { data, error } = await supabase.storage
        .from(bucket)
        .upload(fullPath, file, {
          cacheControl: '3600',
          upsert: false,
        });

      if (error) throw error;

      // Get public URL for public buckets
      const { data: urlData } = supabase.storage
        .from(bucket)
        .getPublicUrl(data.path);

      setUploadProgress(100);

      toast({
        title: 'Upload Complete',
        description: 'File uploaded successfully',
      });

      return { 
        url: urlData.publicUrl, 
        path: data.path, 
        error: null 
      };
    } catch (error) {
      const err = error as Error;
      toast({
        title: 'Upload Failed',
        description: err.message,
        variant: 'destructive',
      });
      return { url: '', path: '', error: err };
    } finally {
      setIsUploading(false);
    }
  }, [user, toast]);

  const remove = useCallback(async (bucket: StorageBucket, path: string) => {
    if (!user) {
      return { error: new Error('You must be logged in to delete files') };
    }

    try {
      const { error } = await supabase.storage.from(bucket).remove([path]);
      if (error) throw error;

      toast({
        title: 'File Deleted',
        description: 'File removed successfully',
      });

      return { error: null };
    } catch (error) {
      const err = error as Error;
      toast({
        title: 'Delete Failed',
        description: err.message,
        variant: 'destructive',
      });
      return { error: err };
    }
  }, [user, toast]);

  const getPublicUrl = useCallback((bucket: StorageBucket, path: string): string => {
    const { data } = supabase.storage.from(bucket).getPublicUrl(path);
    return data.publicUrl;
  }, []);

  const getSignedUrl = useCallback(async (
    bucket: StorageBucket, 
    path: string, 
    expiresIn: number = 3600
  ): Promise<string | null> => {
    const { data, error } = await supabase.storage
      .from(bucket)
      .createSignedUrl(path, expiresIn);

    if (error) {
      console.error('Failed to get signed URL:', error);
      return null;
    }

    return data.signedUrl;
  }, []);

  const listFiles = useCallback(async (
    bucket: StorageBucket, 
    folder?: string
  ): Promise<{ name: string; url: string }[]> => {
    if (!user) return [];

    const path = folder ? `${user.id}/${folder}` : user.id;

    const { data, error } = await supabase.storage
      .from(bucket)
      .list(path);

    if (error) {
      console.error('Failed to list files:', error);
      return [];
    }

    return data
      .filter(item => !item.id.endsWith('/')) // Exclude folders
      .map(item => ({
        name: item.name,
        url: getPublicUrl(bucket, `${path}/${item.name}`),
      }));
  }, [user, getPublicUrl]);

  return {
    upload,
    remove,
    getPublicUrl,
    getSignedUrl,
    listFiles,
    isUploading,
    uploadProgress,
  };
}
